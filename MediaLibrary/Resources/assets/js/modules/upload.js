import debounce from 'lodash/debounce'
import Dropzone from 'dropzone'

export default {
    computed: {
        uploadPanelImg() {
            if (this.uploadArea) {
                let imgs = this.uploadPanelImgList
                let grds = this.uploadPanelGradients

                let url = imgs.length ? imgs[Math.floor(Math.random() * imgs.length)] : null
                let color = grds[Math.floor(Math.random() * grds.length)]

                return url
                    ? {'--gradient': color, 'background-image': `url("${url}")`}
                    : {'--gradient': color}
            }

            return {}
        },
        uploadPreviewListSize() {
            let size = this.uploadPreviewList
                    .map((el) => el.size)
                    .reduce((a, b) => a + b, 0)

            return size ? this.getFileSize(size) : 0
        }
    },
    methods: {
        // dropzone
        fileUpload() {
            let uploaded = 0
            let allFiles = 0
            let uploadProgress = 0

            let library = this
            let queueFix = false
            let last = null
            let uploadPreview = '#uploadPreview'
            let uploadSize = this.getResrtictedUploadSize() || 256
            let uploadTypes = this.getResrtictedUploadTypes()?.join(',') || null
            let autoProcess = this.config.previewFilesBeforeUpload
                ? {
                    autoProcessQueue: false,
                    maxThumbnailFilesize: 25, // mb
                    createImageThumbnails: true,
                    addRemoveLinks: true,
                    dictRemoveFile: '<button class="button is-danger"><span>✘</span></button>',
                    init: function () {
                        let previewContainer = document.querySelector(uploadPreview)

                        // cancel pending upload
                        EventHub.listen('clear-pending-upload', this.removeAllFiles(true))

                        // remove
                        this.on('removedfile', debounce((file) => {
                            library.uploadPreviewOptionsList.some((item, i) => {
                                if (item.name == file.name) {
                                    library.uploadPreviewOptionsList.splice(i, 1)
                                }
                            })

                            if (!this.files.length) {
                                library.clearUploadPreview(previewContainer)
                            }

                            library.uploadPreviewList = this.files
                        }, 100))

                        // add
                        this.on('addedfile', (file) => {
                            let fileList = this.files

                            // remove duplicate files from selection
                            // https://stackoverflow.com/a/32890783/3574919
                            if (fileList.length) {
                                let _i = 0
                                let _len = fileList.length - 1 // -1 to exclude current file
                                for (_i; _i < _len; _i++) {
                                    if (fileList[_i] === file) {
                                        this.removeFile(file)
                                    }
                                }
                            }

                            let el = file.previewElement

                            library.addToPreUploadedList(file)
                            library.uploadPreviewList = fileList
                            library.uploadArea = false
                            library.toolBar = false
                            library.infoSidebar = false
                            library.waitingForUpload = true

                            el.classList.add('is-hidden')
                            previewContainer.classList.add('show')

                            // get around https://www.dropzonejs.com/#config-maxThumbnailFilesize
                            if (!file.dataURL) {
                                let img = el.querySelector('img')
                                img.src = './assets/vendor/Medialibrary/noPreview.jpg'
                                img.style.height = '120px'
                                img.style.width = '120px'

                                el.dataset.name = file.name
                                el.classList.remove('is-hidden')

                                library.$nextTick(() => {
                                    el.querySelector('.dz-image').addEventListener('click', library.changeUploadPreviewFile)
                                })
                            }
                        })

                        // upload preview
                        this.on('thumbnail', (file, dataUrl) => {
                            file.previewElement.classList.remove('is-hidden')
                        })

                        // reset dz
                        library.$refs['clear-dropzone'].addEventListener('click', () => {
                            this.removeAllFiles()
                            library.clearUploadPreview(previewContainer)
                        })

                        // start the upload
                        library.$refs['process-dropzone'].addEventListener('click', () => {
                            // because dz is dump
                            // https://stackoverflow.com/questions/18059128/dropzone-js-uploads-only-two-files-when-autoprocessqueue-set-to-false
                            queueFix = true
                            this.options.autoProcessQueue = true

                            this.processQueue()
                            library.clearUploadPreview(previewContainer)
                        })
                    }
                }
                : {
                    init: function () {
                        this.on('addedfile', (file) => {
                            library.addToPreUploadedList(file)
                        })
                    }
                }

            let options = {
                url: library.routes.upload,
                parallelUploads: 10,
                hiddenInputContainer: '#new-upload',
                uploadMultiple: true,
                forceFallback: false,
                acceptedFiles: uploadTypes,
                maxFilesize: uploadSize,
                headers: {
                    'X-Socket-Id': library.browserSupport('Echo') ? Echo.socketId() : null,
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                timeout: 3600000, // 60 mins
                autoProcessQueue: true,
                previewsContainer: `${uploadPreview} .sidebar`,
                accept: function (file, done) {
                    if (this.getUploadingFiles().length) {
                        return done(library.trans('upload_in_progress'))
                    }

                    if (library.checkPreUploadedList(file)) {
                        return done(library.trans('already_exists'))
                    }

                    allFiles++
                    done()
                },
                sending: function (file, xhr, formData) {
                    uploadProgress += parseFloat(100 / allFiles)
                    library.progressCounter = `${Math.round(uploadProgress)}%`

                    // send files custom options
                    formData.append('custom_attrs', JSON.stringify(library.uploadPreviewOptionsList))
                },
                processingmultiple() {
                    library.showProgress = true
                },
                successmultiple(files, res) {
                    res.map((item) => {
                        uploaded++

                        if (item.success) {
                            last = item.file_name
                            let msg = library.restrictModeIsOn
                                ? `"${item.file_name}"`
                                : `"${item.file_name}" at "${library.files.path}"`

                            library.showNotif(`${library.trans('upload_success')} ${msg}`)
                        } else {
                            library.showNotif(item.message, 'danger')
                        }
                    })
                },
                errormultiple: function (file, res) {
                    file = Array.isArray(file) ? file[0] : file
                    library.showNotif(`"${file.name}" ${res}`, 'danger')
                    this.removeFile(file)
                },
                queuecomplete: function () {
                    if (uploaded == this.files.length) {
                        library.progressCounter = '100%'
                        library.hideProgress()

                        // reset dz
                        if (queueFix) this.options.autoProcessQueue = false
                        this.removeAllFiles()
                        uploaded = 0
                        allFiles = 0

                        last
                            ? library.getFiles(null, last)
                            : library.getFiles()
                    }
                }
            }

            options = Object.assign(options, autoProcess)

            // upload panel
            new Dropzone('#new-upload', options)
            // drag & drop on empty area
            new Dropzone('.__stack-container', Object.assign(options, {clickable: false}))
        },

        clearUploadPreview(previewContainer) {
            previewContainer.classList.remove('show')

            this.$nextTick(() => {
                this.waitingForUpload = false
                this.toolBar = true
                this.smallScreenHelper()
                this.resetInput([
                    'uploadPreviewList',
                    'uploadPreviewNamesList',
                    'uploadPreviewOptionsList'
                ], [])
                this.resetInput('selectedUploadPreviewName')
            })
        },

        // already uploaded checks
        checkPreUploadedList(file) {
            return this.uploadPreviewNamesList.some((name) => name == file.name)
        },
        addToPreUploadedList(file) {
            this.filesNamesList.some((name) => {
                if (name == file.name && !this.checkPreUploadedList(file)) {
                    this.uploadPreviewNamesList.push(name)
                }
            })
        },
        checkForUploadedFile(name) {
            return this.uploadPreviewList.some((file) => file.name == name)
        },

        // show large preview
        changeUploadPreviewFile(e) {
            e.stopPropagation()

            let box = e.target
            let container = box.closest('.dz-preview')

            if (container) {
                let name = container.dataset.name

                if (this.checkForUploadedFile(name)) {
                    this.selectedUploadPreviewName = name

                    // illuminate selected preview
                    this.$nextTick(() => {
                        let active = document.querySelector('.is-previewing')

                        if (active) active.classList.remove('is-previewing')
                        box.classList.add('is-previewing')
                    })
                }

            }
        },

        // upload image from link
        saveLinkForm(event) {
            let url = this.urlToUpload

            if (!url) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            this.uploadArea = false
            this.toggleLoading()
            this.loadingFiles('show')

            this.$nextTick(() => {
                axios.post(event.target.action, {
                    path: this.files.path,
                    url: url,
                    random_names: this.useRandomNamesForUpload
                }).then(({data}) => {
                    this.toggleLoading()
                    this.loadingFiles('hide')

                    if (!data.success) {
                        return this.showNotif(data.message, 'danger')
                    }

                    this.resetInput('urlToUpload')
                    this.$nextTick(() => this.$refs.save_link_modal_input.focus())
                    this.showNotif(`${this.trans('save_success')} "${data.message}"`)
                    this.getFiles(null, data.message)

                }).catch((err) => {
                    console.error(err)

                    this.toggleLoading()
                    this.toggleModal()
                    this.loadingFiles('hide')
                    this.ajaxError()
                })
            })
        }
    }
}
