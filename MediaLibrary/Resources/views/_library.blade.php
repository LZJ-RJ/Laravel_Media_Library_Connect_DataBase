{{-- component --}}
<media-library inline-template
    v-cloak
    class="hide-native-scrollbar"
    :config="{{ json_encode([
        'baseUrl' => $base_url,
        'hideFilesExt' => config('medialibrary.hide_files_ext'),
        'mimeTypes' => config('medialibrary.extended_mimes'),
        'broadcasting' => config('medialibrary.enable_broadcasting'),
        'gfi' => config('medialibrary.get_folder_info'),
        'ratioBar' => config('medialibrary.show_ratio_bar'),
        'previewFilesBeforeUpload' => config('medialibrary.preview_files_before_upload')
    ]) }}"
    :routes="{{ json_encode([
        'files' => route('mediaLibrary.get_files'),
        'lock' => route('mediaLibrary.lock_file'),
        'visibility' => route('mediaLibrary.change_vis'),
        'upload' => route('mediaLibrary.upload'),
        'locked_list' => route('mediaLibrary.locked_list')
    ]) }}"
    :translations="{{ json_encode([
        'add_to_list' => trans('medialibrary::messages.add.list'),
        'added' => trans('medialibrary::messages.add.added'),
        'already_exists' => trans('medialibrary::messages.error.already_exists'),
        'application' => trans('medialibrary::messages.application'),
        'audio' => trans('medialibrary::messages.audio.main'),
        'bm_add_to_list' => trans('medialibrary::messages.bookmarks.add'),
        'bm' => trans('medialibrary::messages.bookmarks.main'),
        'clear' => trans('medialibrary::messages.clear', ['attr' => 'selection']),
        'copy_success' => trans('medialibrary::messages.copy.success'),
        'copy' => trans('medialibrary::messages.copy.main'),
        'create_folder_notif' => trans('medialibrary::messages.new.create_folder_notif'),
        'create_success' => trans('medialibrary::messages.create_success'),
        'crop_apply' => trans('medialibrary::messages.crop.apply'),
        'crop_flip_horizontal' => trans('medialibrary::messages.crop.flip_horizontal'),
        'crop_flip_vertical' => trans('medialibrary::messages.crop.flip_vertical'),
        'crop_reset_filters' => trans('medialibrary::messages.crop.reset_filters'),
        'crop_reset' => trans('medialibrary::messages.crop.reset'),
        'crop_rotate_left' => trans('medialibrary::messages.crop.rotate_left'),
        'crop_rotate_right' => trans('medialibrary::messages.crop.rotate_right'),
        'crop_zoom_in' => trans('medialibrary::messages.crop.zoom_in'),
        'crop_zoom_out' => trans('medialibrary::messages.crop.zoom_out'),
        'crop' => trans('medialibrary::messages.crop.main'),
        'delete_success' => trans('medialibrary::messages.delete.success'),
        'delete' => trans('medialibrary::messages.delete.main'),
        'description' => trans('medialibrary::messages.description'),
        'diff' => trans('medialibrary::messages.editor.diff'),
        'dimension' => trans('medialibrary::messages.dimension'),
        'downloaded' => trans('medialibrary::messages.download.downloaded'),
        'editor' => trans('medialibrary::messages.editor.main'),
        'error_altered_fwli' => trans('medialibrary::messages.error.altered_fwli'),
        'filter_by' => trans('medialibrary::messages.filter.by', ['attr' => '']),
        'filtration' => trans('medialibrary::messages.filter.filtration'),
        'find' => trans('medialibrary::messages.find'),
        'focals' => trans('medialibrary::messages.focals'),
        'folder' => trans('medialibrary::messages.folder'),
        'found' => trans('medialibrary::messages.found'),
        'glbl_search_avail' => trans('medialibrary::messages.search.glbl_avail'),
        'glbl_search' => trans('medialibrary::messages.search.glbl'),
        'go_to_folder' => trans('medialibrary::messages.go_to_folder'),
        'image' => trans('medialibrary::messages.image'),
        'last_modified' => trans('medialibrary::messages.last_modified'),
        'locked' => trans('medialibrary::messages.locked'),
        'move_clear' => trans('medialibrary::messages.move.clear_list'),
        'move_success' => trans('medialibrary::messages.move.success'),
        'move' => trans('medialibrary::messages.move.main'),
        'name' => trans('medialibrary::messages.name'),
        'new_uploads_notif' => trans('medialibrary::messages.upload.new_uploads_notif'),
        'no_val' => trans('medialibrary::messages.no_val'),
        'non' => trans('medialibrary::messages.non'),
        'nothing_found' => trans('medialibrary::messages.nothing_found'),
        'open' => trans('medialibrary::messages.open'),
        'options' => trans('medialibrary::messages.options'),
        'presets' => trans('medialibrary::messages.crop.presets'),
        'refresh_notif' => trans('medialibrary::messages.refresh_notif'),
        'rename_success' => trans('medialibrary::messages.rename.success'),
        'rename' => trans('medialibrary::messages.rename.main'),
        'reset' => trans('medialibrary::messages.crop.reset'),
        'save_success' => trans('medialibrary::messages.save.success'),
        'save' => trans('medialibrary::messages.save.main'),
        'selected' => trans('medialibrary::messages.select.selected'),
        'sep_download' => trans('medialibrary::messages.download.sep'),
        'size' => trans('medialibrary::messages.size'),
        'sort_by' => trans('medialibrary::messages.sort_by'),
        'stand_by' => trans('medialibrary::messages.stand_by'),
        'text' => trans('medialibrary::messages.text'),
        'to_cp' => trans('medialibrary::messages.copy.to_cp'),
        'upload_in_progress' => trans('medialibrary::messages.upload.in_progress'),
        'upload_success' => trans('medialibrary::messages.upload.success'),
        'video' => trans('medialibrary::messages.video'),
        'voice_start' => trans('medialibrary::messages.voice.start'),
        'voice_stop' => trans('medialibrary::messages.voice.stop'),
    ]) }}"
    :in-modal="{{ isset($modal) ? 'true' : 'false' }}"
    :hide-ext="{{ isset($hideExt) ? json_encode($hideExt) : '[]' }}"
    :hide-path="{{ isset($hidePath) ? json_encode($hidePath) : '[]' }}"
    :restrict="{{ isset($restrict) ? json_encode($restrict) : '{}' }}"
    :user-id="{{ config('medialibrary.enable_broadcasting') ? optional(auth()->user())->id : 0 }}"
    :upload-panel-img-list="{{ $patterns ?? '[]' }}">

    <div class="media-library"
        :class="[
            {'__stack-reverse': waitingForUpload},
            {'top-space': !inModal}
        ]">

        {{-- content ratio bar --}}
        <transition name="mm-list" mode="out-in">
            <content-ratio v-if="config.ratioBar && allItemsCount"
                :list="allFiles"
                :total="allItemsCount"
                :file-type-is="fileTypeIs">
            </content-ratio>
        </transition>

        {{-- global search --}}
        <global-search-panel
            :trans="trans"
            :file-type-is="fileTypeIs"
            :add-to-movable-list="addToMovableList"
            :in-movable-list="inMovableList"
            :no-scroll="noScroll"
            :browser-support="browserSupport">
        </global-search-panel>

        {{-- usage-intro panel --}}
        <usage-intro-panel :no-scroll="noScroll"></usage-intro-panel>

        {{-- top toolbar --}}
        <transition name="mm-list" mode="out-in">
            <nav class="media-library__toolbar level" v-show="toolBar">

                {{-- left toolbar --}}
                <div class="level-left">
                    {{-- first --}}
                    <div class="level-item">
                        <div class="field" :class="{'has-addons': !isBulkSelecting()}">
                            {{-- upload --}}
                            <div class="control" v-if="!isBulkSelecting()">
                                <button class="button"
                                    ref="upload"
                                    :disabled="isLoading"
                                    @click.stop="toggleUploadPanel()"
                                    v-tippy="{arrow: true}"
                                    title="u">
                                    <span class="icon"><icon name="shopping-basket"></icon></span>
                                    <span>{{ trans('medialibrary::messages.upload.main') }}</span>
                                </button>
                            </div>

                            {{-- new folder --}}
                            <div class="control">
                                <button class="button"
                                    :disabled="isLoading"
                                    @click.stop="createNewFolder()">
                                    <span class="icon"><icon name="folder"></icon></span>
                                    <span>{{ trans('medialibrary::messages.add.folder') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- middle --}}
                    <div class="level-item">
                        <div class="field has-addons">
                            {{-- move --}}
                            <div class="control">
                                <button class="button is-link"
                                    ref="move"
                                    :disabled="isLoading || !movableItemsCount"
                                    v-tippy="{arrow: true}"
                                    title="m / p"
                                    @click.stop="moveItem()">
                                    <span class="icon"><icon name="share" scale="0.8"></icon></span>
                                    <span>{{ trans('medialibrary::messages.move.main') }}</span>
                                </button>
                            </div>

                            {{-- rename --}}
                            <div class="control" v-if="!isBulkSelecting()">
                                <button class="button is-link"
                                    ref="rename"
                                    :disabled="ops_btn_disable"
                                    @click.stop="renameItem()">
                                    <span class="icon"><icon name="terminal"></icon></span>
                                    <span>{{ trans('medialibrary::messages.rename.main') }}</span>
                                </button>
                            </div>

                            {{-- editor --}}
                            <div class="control" v-show="!isBulkSelecting()">
                                <button class="button is-link"
                                    ref="editor"
                                    :disabled="editor_btn_disable"
                                    v-tippy="{arrow: true}"
                                    title="e"
                                    @click.stop="imageEditor()">
                                    <span class="icon"><icon name="object-ungroup" scale="1.2"></icon></span>
                                    <span>{{ trans('medialibrary::messages.editor.main') }}</span>
                                </button>
                            </div>

                            {{-- delete --}}
                            <div class="control">
                                <button class="button is-link"
                                    ref="delete"
                                    :disabled="ops_btn_disable"
                                    v-tippy="{arrow: true}"
                                    title="d / del"
                                    @click.stop="deleteItem()">
                                    <span class="icon"><icon name="trash"></icon></span>
                                    <span>{{ trans('medialibrary::messages.delete.main') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- last --}}
                    <div class="level-item">
                        <div class="field has-addons">
                            {{-- refresh --}}
                            <div class="control" v-if="!isBulkSelecting()">
                                <v-touch class="button is-primary"
                                    ref="refresh"
                                    :disabled="isLoading"
                                    tag="button"
                                    v-tippy="{arrow: true}"
                                    title="(R) efresh"
                                    @tap="refresh()"
                                    @hold="clearAll()">
                                    <span class="icon">
                                        <icon name="refresh" :spin="isLoading"></icon>
                                    </span>
                                </v-touch>
                            </div>

                            {{-- lock --}}
                            <div class="control">
                                <button class="button is-warning"
                                    ref="lock"
                                    :disabled="lock_btn_disable"
                                    v-tippy="{arrow: true}"
                                    title="(L) ock"
                                    @click.stop="lockFileForm()">
                                    <span class="icon">
                                        <icon :name="IsLocked(selectedFile) ? 'lock' : 'unlock'"></icon>
                                    </span>
                                </button>
                            </div>

                            {{-- visibility --}}
                            <div class="control">
                                <button class="button"
                                    :class="IsVisible(selectedFile) ? 'is-light' : 'is-danger'"
                                    ref="visibility"
                                    :disabled="vis_btn_disable"
                                    v-tippy="{arrow: true}"
                                    title="(V) isibility"
                                    @click.stop="FileVisibilityForm()">
                                    <span class="icon">
                                        <icon :name="IsVisible(selectedFile) ? 'eye' : 'eye-slash'"></icon>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ====================================================================== --}}

                {{-- right toolbar --}}
                <div class="level-right" v-if="!waitingForUpload">
                    <div class="level-item">
                        <div class="field" :class="{'has-addons' : isBulkSelecting()}">
                            {{-- bulk select all --}}
                            <div class="control">
                                <button @click.stop="blkSlctAll()"
                                    ref="bulkSelectAll"
                                    class="button"
                                    :class="{'is-warning' : bulkSelectAll}"
                                    v-show="isBulkSelecting()"
                                    :disabled="searchItemsCount == 0 || isLoading"
                                    v-tippy="{arrow: true}"
                                    title="a">
                                    <template v-if="bulkSelectAll">
                                        <span class="icon"><icon name="minus" scale="0.8"></icon></span>
                                        <span>{{ trans('medialibrary::messages.select.non') }}</span>
                                    </template>
                                    <template v-else>
                                        <span class="icon"><icon name="plus" scale="0.8"></icon></span>
                                        <span>{{ trans('medialibrary::messages.select.all') }}</span>
                                    </template>
                                </button>
                            </div>

                            {{-- bulk select --}}
                            <div class="control">
                                <button @click.stop="blkSlct()"
                                    ref="bulkSelect"
                                    class="button"
                                    :class="{'is-danger' : bulkSelect}"
                                    :disabled="searchItemsCount == 0 || !allItemsCount || isLoading"
                                    v-tippy="{arrow: true}"
                                    title="b">
                                    <span class="icon"><icon name="puzzle-piece"></icon></span>
                                    <span>{{ trans('medialibrary::messages.select.bulk') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <template>
                       {{-- filter & sort --}}
                        <div class="level-item" v-if="searchItemsCount != 0 && allItemsCount">
                            <filter-and-sorting :disabled="isLoading"
                                :filter-name-is="filterNameIs"
                                :sort-name-is="sortNameIs"
                                :set-filter-name="setFilterName"
                                :set-sort-name="setSortName"
                                :have-a-file-of-type="haveAFileOfType"
                                :trans="trans">
                            </filter-and-sorting>
                        </div>

                        {{-- dir bookmarks --}}
                        <div class="level-item" v-if="!restrictModeIsOn && firstRun">
                            <dir-bookmarks :disabled="isLoading"
                                :dir-bookmarks="dirBookmarks"
                                :path="files.path"
                                :trans="trans">
                            </dir-bookmarks>
                        </div>

                        {{-- search --}}
                        <div class="level-item" v-if="allItemsCount">
                            <div class="control">
                                <div class="field has-addons">
                                    {{-- global --}}
                                    <p class="control" v-if="!restrictModeIsOn">
                                        <global-search-btn
                                            route="{{ route('mediaLibrary.global_search') }}"
                                            :is-loading="isLoading"
                                            :trans="trans"
                                            :show-notif="showNotif">
                                        </global-search-btn>
                                    </p>

                                    {{-- voice --}}
                                    <p class="control">
                                        <voice-search :trans="trans" :search-for="searchFor"></voice-search>
                                    </p>

                                    {{-- local --}}
                                    <p class="control has-icons-left">
                                        <input class="input"
                                            :disabled="isLoading"
                                            type="text"
                                            ref="search"
                                            v-model="searchFor"
                                            :placeholder="trans('find')">
                                        <span class="icon is-left">
                                            <icon name="search"></icon>
                                        </span>
                                    </p>

                                    {{-- clear --}}
                                    <p class="control">
                                        <button class="button is-black" :disabled="!searchFor"
                                            v-tippy="{arrow: true}"
                                            title="{{ trans('medialibrary::messages.clear', ['attr' => trans('medialibrary::messages.search.main')]) }}"
                                            @click.stop="resetInput('searchFor')">
                                            <span class="icon"><icon name="times"></icon></span>
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </nav>
        </transition>

        {{-- ====================================================================== --}}

        {{-- dropzone --}}
        <section>
            <div class="media-library__dz" :class="{'__dz-active': uploadArea}">
                <form id="new-upload" :style="uploadPanelImg">
                    <input type="hidden" name="upload_path" :value="files.path">
                    <input type="hidden" name="random_names" :value="useRandomNamesForUpload">

                    {{-- text --}}
                    <div class="dz-message title is-4">{!! trans('medialibrary::messages.upload.text') !!}</div>

                    {{-- randomNames --}}
                    {{-- 去掉這部分，因為中文一樣不會因為此開關而有效。--}}
{{--                    <div class="form-switcher"--}}
{{--                        title="{{ trans('medialibrary::messages.upload.use_random_names') }}"--}}
{{--                        v-tippy="{arrow: true, placement: 'right'}">--}}
{{--                        <input type="checkbox" id="random_names" v-model="useRandomNamesForUpload">--}}
{{--                        <label class="switcher" for="random_names"></label>--}}
{{--                    </div>--}}

                    {{-- urlToUpload --}}
                    <div class="save_link" @click.stop="toggleModal('save_link_modal')" v-if="!restrictUpload()">
                        <span class="icon is-large"
                            title="{{ trans('medialibrary::messages.save.link') }}"
                            v-tippy="{arrow: true, placement: 'left'}">
                            <icon>
                                <icon class="circle" name="circle" scale="2.5"></icon>
                                <icon class="anchor" name="link"></icon>
                            </icon>
                        </span>
                    </div>
                </form>
            </div>

            <transition name="mm-list">
                <div v-show="showProgress" id="uploadProgress" class="progress">
                    <div class="progress-bar is-success progress-bar-striped active" :style="{width: progressCounter}"></div>
                </div>
            </transition>
        </section>

        {{-- ====================================================================== --}}

        {{-- mobile breadCrumb --}}
        @include('medialibrary::partials.mobile-nav')

        {{-- ====================================================================== --}}

        <div class="media-library__stack">
            <section class="__stack-container">

                {{-- upload preview --}}
                <div id="uploadPreview">
                    {{-- ops --}}
                    <div class="dz-preview-ops btn-animate extra-func-btns">
                        {{-- add more files --}}
                        <button v-tippy="{arrow: true, placement: 'left'}"
                            title="{{ trans('medialibrary::messages.add.more', ['attr' => null]) }} (u)"
                            @click.stop="toggleUploadPanel()"
                            class="btn-plain">
                            <span class="icon is-large">
                                <icon>
                                    <icon name="circle" scale="2.5"></icon>
                                    <icon class="icon-btn" name="cloud-upload"/>
                                </icon>
                            </span>
                        </button>
                        {{-- upload --}}
                        <button v-tippy="{arrow: true, placement: 'left'}"
                            title="{{ trans('medialibrary::messages.upload.main') }} (Enter)"
                            ref="process-dropzone"
                            class="btn-plain">
                            <span class="icon is-large">
                                <icon>
                                    <icon name="circle" scale="2.5"></icon>
                                    <icon class="icon-btn" name="check"/>
                                </icon>
                            </span>
                        </button>
                        {{-- reset --}}
                        <button v-tippy="{arrow: true, placement: 'left'}"
                            title="{{ trans('medialibrary::messages.clear', ['attr' => null]) }} (Esc)"
                            ref="clear-dropzone"
                            class="btn-plain">
                            <span class="icon is-large">
                                <icon>
                                    <icon name="circle" scale="2.5"></icon>
                                    <icon class="icon-btn" name="times"/>
                                </icon>
                            </span>
                        </button>
                    </div>

                    {{-- preview --}}
                    <section class="sidebar-container">
                        <div class="sidebar"></div>
                    </section>
                    <template v-for="file in uploadPreviewList">
                        <keep-alive v-if="checkForUploadedFile(file.name)">
                            <upload-preview v-if="file.name == selectedUploadPreviewName"
                                :key="file.name"
                                :file="file"
                                :file-type-is="fileTypeIs"
                                :trans="trans">
                            </upload-preview>
                        </keep-alive>
                    </template>
                </div>

                {{-- loadings --}}
                <div>
                    {{-- loading data from server --}}
                    <div id="loading_files" v-show="loading_files">
                        <div id="loading_files_anim" data-json=""></div>

                        <transition name="mm-list" mode="out-in">
                            <h3 key="1" v-if="showProgress" class="mm-animated pulse">
                                {{ trans('medialibrary::messages.stand_by') }}
                                <strong>@{{ progressCounter }}</strong>
                            </h3>
                            <h3 key="2" v-else>{{ trans('medialibrary::messages.loading') }}</h3>
                        </transition>
                    </div>

                    {{-- ajax error --}}
                    <div id="ajax_error" v-show="ajax_error">
                        <div id="ajax_error_anim" data-json="{{ asset('assets/vendor/MediaLibrary/lottie/avalanche.json') }}"></div>
                        <h3>{{ trans('medialibrary::messages.ajax_error') }}</h3>
                    </div>

                    {{-- no files --}}
                    <v-touch id="no_files"
                        v-show="no_files"
                        class="no_files"
                        @swiperight="goToPrevFolder()"
                        @swipeleft="goToPrevFolder()"
                        @hold="containerClick($event, 'no_files')"
                        @dbltap="containerClick($event, 'no_files')">
                        <div id="no_files_anim" data-json="{{ asset('assets/vendor/MediaLibrary/lottie/zero.json') }}"></div>
                        <h3>{{ trans('medialibrary::messages.no_files_in_folder') }}</h3>
                    </v-touch>
                </div>

                {{-- overlay --}}
                <container-click-overlay></container-click-overlay>

                <div class="extra-func-btns">
                    {{-- intro --}}
                    <usage-intro-btn v-show="!isLoading && !waitingForUpload"></usage-intro-btn>

                    {{-- movable list --}}
                    <v-touch v-if="allItemsCount && !isLoading && !waitingForUpload"
                        class="movable-list"
                        v-tippy="{arrow: true, hideOnClick: false}"
                        :title="inMovableList() ? '{{ trans('medialibrary::messages.add.added') }}' : '{{ trans('medialibrary::messages.add.list') }} (c / x)'"
                        @tap="addToMovableList()"
                        @dbltap="showMovableList()"
                        @hold="clearMovableList()">
                        <span class="icon is-large">
                            <i class="fas fa-edit"></i>
                        </span>
                        <span class="counter">@{{ movableItemsCount || null }}</span>
                    </v-touch>
                </div>

                {{-- ====================================================================== --}}

                {{-- files box --}}
                <v-touch class="__stack-files mm-animated"
                    :class="{'__stack-sidebar-hidden' : !infoSidebar}"
                    ref="__stack-files"
                    @swiperight="goToPrevFolder()"
                    @swipeleft="goToPrevFolder()"
                    @hold="containerClick($event)"
                    @dbltap="containerClick($event)"
                    @pinchin="containerClick($event)">

                    {{-- no search --}}
                    <section>
                        <div id="no_search" v-show="no_search">
                            <div id="no_search_anim" data-json="{{ asset('assets/vendor/MediaLibrary/lottie/ice_cream.json') }}"></div>
                            <h3>@{{ trans('nothing_found') }}</h3>
                        </div>
                    </section>

                    {{-- files --}}
                    <ul class="__files-boxs" ref="filesList">
                        <li v-for="(file, index) in orderBy(filterBy(allFiles, searchFor, 'name'), sortName, sortDirection)"
                            :key="file.name"
                            :data-file-index="index"
                            @click.stop="setSelected(file, index, $event)">
                            <v-touch class="__file-box mm-animated"
                                :class="{'bulk-selected': IsInBulkList(file), 'selected' : isSelected(file)}"
                                @swipeup="swipGesture($event, file, index)"
                                @swipedown="swipGesture($event, file, index)"
                                @swiperight="swipGesture($event, file, index)"
                                @swipeleft="swipGesture($event, file, index)"
                                @hold="pressGesture($event, file, index)"
                                @dbltap="pressGesture($event, file, index)">

                                {{-- lock file --}}
                                <button v-if="$refs.lock"
                                    class="__box-lock-icon icon"
                                    :disabled="isLoading"
                                    :class="IsLocked(file) ? 'is-danger' : 'is-success'"
                                    :title="IsLocked(file) ? '{{ trans('medialibrary::messages.unlock') }}' : '{{ trans('medialibrary::messages.lock') }}'"
                                    v-tippy="{arrow: true, hideOnClick: false}"
                                    @click.stop="lockFileForm(file)">
                                </button>

                                {{-- copy file link --}}
                                <div v-if="!fileTypeIs(file, 'folder')"
                                    class="__box-copy-link icon"
                                    @click.stop="copyLink(file.path)"
                                    :title="linkCopied ? trans('copied') : trans('to_cp')"
                                    v-tippy="{arrow: true, hideOnClick: false}"
                                    @hidden="linkCopied = false">
                                    <icon name="clone" scale="0.9"></icon>
                                </div>

                                <div class="__box-data">
                                    <div class="__box-preview">
                                        <template v-if="fileTypeIs(file, 'image')">
                                            <image-intersect
                                                :file="file"
                                                :check-for-dimensions="checkForDimensions"
                                                :browser-support="browserSupport"
                                                root-el=".__stack-files">
                                            </image-intersect>
                                        </template>

                                        <span v-else class="icon is-large">
                                            <icon v-if="fileTypeIs(file, 'folder')" name="folder" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'application')" name="cogs" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'compressed')" name="file-archive-o" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'video')" name="film" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'audio')" name="music" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'pdf')" name="file-pdf-o" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'text')" name="file-text-o" scale="2.6"></icon>
                                        </span>
                                    </div>

                                    <div class="__box-info">
                                        {{-- folder --}}
                                        <template v-if="fileTypeIs(file, 'folder')">
                                            <h4>@{{ file.name }}</h4>
                                            <small v-if="config.gfi">
                                                <span>@{{ file.count }} {{ trans('medialibrary::messages.items') }}</span>
                                                <span v-if="file.size > 0" class="__info-file-size">"@{{ getFileSize(file.size) }}"</span>
                                            </small>
                                        </template>

                                        {{-- any other --}}
                                        <template v-else>
                                            <h4>@{{ getFileName(file.name) }}</h4>
                                            <small class="__info-file-size">@{{ getFileSize(file.size) }}</small>
                                        </template>
                                    </div>
                                </div>
                            </v-touch>
                        </li>
                    </ul>

                    <infinite-loading v-if="firstRun && files.next"
                                    spinner="waveDots"
                                    @infinite="loadPaginatedFiles">
                        <span slot="no-more"></span>
                    </infinite-loading>
                </v-touch>

                {{-- ====================================================================== --}}

                {{-- info sidebar --}}
                <v-touch v-if="infoSidebar"
                    class="__stack-sidebar is-hidden-touch"
                    @swiperight="toggleInfoSidebar(), saveUserPref()"
                    @swipeleft="toggleInfoSidebar(), saveUserPref()">

                    {{-- preview --}}
                    <div class="__sidebar-preview">
                        <transition name="mm-slide" mode="out-in" appear>
                            {{-- no selection --}}
                            <div key="none-selected" class="__sidebar-none-selected" v-if="!selectedFile">
                                <span @click.stop="reset()" class="link"><icon name="power-off" scale="3.2"></icon></span>
                                <p>{{ trans('medialibrary::messages.select.nothing') }}</p>
                            </div>

                            {{-- img --}}
                            <image-preview v-else-if="selectedFileIs('image')"
                                v-tippy="{arrow: true, placement: 'left'}"
                                title="{{ trans('medialibrary::messages.space') }}"
                                :key="selectedFile.storage_path">

                                <img :src="selectedFile.path"
                                    :alt="selectedFile.name"
                                    class="link image"
                                    @click.stop="isBulkSelecting() ? false : toggleModal('preview_modal')"/>
                            </image-preview>

                            {{-- video --}}
                            <div v-else-if="selectedFileIs('video')"
                                v-tippy="{arrow: true, placement: 'left'}"
                                title="{{ trans('medialibrary::messages.space') }}"
                                :key="selectedFile.storage_path">
                                <video controls
                                    playsinline
                                    @loadedmetadata="saveVideoDimensions"
                                    preload="metadata"
                                    data-player
                                    :src="selectedFile.path">
                                    {{ trans('medialibrary::messages.video_support') }}
                                </video>
                            </div>

                            {{-- audio --}}
                            <div v-else-if="selectedFileIs('audio')"
                                v-tippy="{arrow: true, placement: 'left'}"
                                title="{{ trans('medialibrary::messages.space') }}"
                                :key="selectedFile.storage_path">
                                <template>
                                    <img v-if="audioFileMeta && audioFileMeta.cover"
                                        :src="audioFileMeta.cover"
                                        :alt="selectedFile.name"
                                        class="image"/>
                                    <icon v-else class="svg-prev-icon" name="music" scale="8"></icon>
                                </template>

                                <audio controls
                                    class="is-hidden"
                                    preload="none"
                                    data-player
                                    :src="selectedFile.path">
                                    {{ trans('medialibrary::messages.audio.support') }}
                                </audio>
                            </div>

                            {{-- icons --}}
                            <div key="pdf" v-else-if="selectedFileIs('pdf')"
                                class="link"
                                v-tippy="{arrow: true, placement: 'left'}"
                                title="{{ trans('medialibrary::messages.space') }}"
                                @click.stop="toggleModal('preview_modal')">
                                <icon class="svg-prev-icon" name="file-pdf-o" scale="4"></icon>
                            </div>

                            <div key="text" v-else-if="selectedFileIs('text')"
                                class="link"
                                v-tippy="{arrow: true, placement: 'left'}"
                                title="{{ trans('medialibrary::messages.space') }}"
                                @click.stop="toggleModal('preview_modal')">
                                <icon class="svg-prev-icon" name="file-text-o" scale="4"></icon>
                            </div>

                            <icon-types v-else
                                classes="svg-prev-icon"
                                :file="selectedFile"
                                :file-type-is="fileTypeIs"
                                :scale="4"
                                :except="['image', 'audio', 'video', 'pdf', 'text']"/>
                        </transition>
                    </div>

                    {{-- info --}}
                    <div v-if="allItemsCount"
                        class="__sidebar-info"
                        :style="{'background-color': selectedFile ? 'white' : ''}">

                        <transition name="mm-list" mode="out-in" appear>
                            <div :key="selectedFile.name" v-if="selectedFile">
                                {{-- audio extra info --}}
                                <template v-if="selectedFileIs('audio') && checkAudioData()">
                                    <table>
                                        <tbody>
                                            <tr v-if="audioFileMeta.artist">
                                                <td class="t-key">{{ trans('medialibrary::messages.audio.artist') }}:</td>
                                                <td class="t-val">@{{ audioFileMeta.artist }}</td>
                                            </tr>
                                            <tr v-if="audioFileMeta.title">
                                                <td class="t-key">{{ trans('medialibrary::messages.audio.title') }}:</td>
                                                <td class="t-val">@{{ audioFileMeta.title }}</td>
                                            </tr>
                                            <tr v-if="audioFileMeta.album">
                                                <td class="t-key">{{ trans('medialibrary::messages.audio.album') }}:</td>
                                                <td class="t-val">@{{ audioFileMeta.album }}</td>
                                            </tr>
                                            <tr v-if="audioFileMeta.track">
                                                <td class="t-key">{{ trans('medialibrary::messages.audio.track') }}:</td>
                                                <td class="t-val">@{{ audioFileMeta.track }} <span v-if="audioFileMeta.track_total">/ @{{ audioFileMeta.track_total }}</span></td>
                                            </tr>
                                            <tr v-if="audioFileMeta.year">
                                                <td class="t-key">{{ trans('medialibrary::messages.audio.year') }}:</td>
                                                <td class="t-val">@{{ audioFileMeta.year }}</td>
                                            </tr>
                                            <tr v-if="audioFileMeta.genre">
                                                <td class="t-key">{{ trans('medialibrary::messages.audio.genre') }}:</td>
                                                <td class="t-val">@{{ audioFileMeta.genre }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <hr>
                                </template>

                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="t-key">{{ trans('medialibrary::messages.name') }}:</td>
                                            <td class="t-val">@{{ selectedFile.name }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="t-key">{{ trans('medialibrary::messages.type') }}:</td>
                                            <td class="t-val">@{{ selectedFile.type }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="t-key">{{ trans('medialibrary::messages.size') }}:</td>
                                            <td class="t-val">@{{ getFileSize(selectedFile.size) }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                {{-- folder --}}
                                <template v-if="selectedFileIs('folder')">
                                    <table v-if="config.gfi">
                                        <tbody>
                                            <tr>
                                                <td class="t-key">{{ trans('medialibrary::messages.items') }}:</td>
                                                <td class="t-val">@{{ selectedFile.count }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="__sidebar-zip" v-show="!isBulkSelecting()">
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td class="t-key">{{ trans('medialibrary::messages.download.folder') }}:</td>
                                                    <td class="t-val">
                                                        <form action="{{ route('mediaLibrary.folder_download') }}" method="post" @submit.prevent="ZipDownload($event)">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="folders" :value="files.path">
                                                            <input type="hidden" name="name" :value="selectedFile.name">
                                                            <button type="submit" class="btn-plain zip" :disabled="config.gfi && selectedFile.count == 0">
                                                                <span class="icon"><icon name="archive" scale="1.2"></icon></span>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>

                                {{-- file --}}
                                <template v-else>
                                    <table v-if="(selectedFileIs('image') || selectedFileIs('video')) && dimensions.length">
                                        <tbody>
                                            <tr>
                                                <td class="t-key">{{ trans('medialibrary::messages.dimension') }}:</td>
                                                <td class="t-val">@{{ selectedFileDimensions }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td class="t-key">{{ trans('medialibrary::messages.visibility.main') }}:</td>
                                                <td class="t-val">@{{ selectedFile.visibility }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td class="t-key">{{ trans('medialibrary::messages.preview') }}:</td>
                                                <td class="t-val"><a :href="selectedFile.path" target="_blank">{{ trans('medialibrary::messages.public_url') }}</a></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="__sidebar-zip">
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td class="t-key">{{ trans('medialibrary::messages.download.file') }}:</td>
                                                    {{-- normal --}}
                                                    <td class="t-val">
                                                        <button class="btn-plain" @click.prevent="saveFile(selectedFile)">
                                                            <span class="icon"><icon name="download" scale="1.2"></icon></span>
                                                        </button>
                                                    </td>
                                                    {{-- zip --}}
                                                    <td class="t-val">
                                                        <form action="{{ route('mediaLibrary.files_download') }}"
                                                            method="post"
                                                            @submit.prevent="ZipDownload($event)"
                                                            v-show="isBulkSelecting()">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="list" :value="JSON.stringify(bulkList)">
                                                            <input type="hidden" name="name" :value="folders.length ? folders[folders.length - 1] : 'media_library'">
                                                            <button type="submit" class="btn-plain zip" :disabled="hasFolder()">
                                                                <span class="icon"><icon name="archive" scale="1.2"></icon></span>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>

                                <table v-if="selectedFile.last_modified_formated">
                                    <tbody>
                                        <tr>
                                            <td class="t-key">{{ trans('medialibrary::messages.last_modified') }}:</td>
                                            <td class="t-val">@{{ selectedFile.last_modified_formated }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- keep the counts at bottom --}}
                            <div v-else></div>
                        </transition>

                        {{-- items count --}}
                        <div class="__sidebar-count">
                            {{-- all --}}
                            <div key="1" v-if="allItemsCount">
                                <p class="title is-1">@{{ allItemsCount }}</p>
                                <p class="heading">{{ trans('medialibrary::messages.total') }}</p>
                            </div>

                            {{-- search --}}
                            <div key="2" v-if="searchItemsCount !== null && searchItemsCount >= 0">
                                <p class="title is-1">@{{ searchItemsCount }}</p>
                                <p class="heading">@{{ trans('found') }}</p>
                            </div>

                            {{-- bulk --}}
                            <div key="3" v-if="bulkItemsCount" class="__sidebar-count-bulk">
                                <p>
                                    <span class="title is-1">@{{ bulkItemsCount }}</span>

                                    {{-- nested --}}
                                    <template v-if="bulkItemsChild">
                                        <span class="icon is-medium"><icon name="plus"></icon></span>
                                        <span class="title is-5">@{{ bulkItemsChild }}</span>
                                    </template>
                                </p>

                                {{-- size --}}
                                <p v-show="bulkItemsSize" class="title is-6 has-text-weight-semibold">@{{ bulkItemsSize }}</p>
                                <p class="heading">{{ trans('medialibrary::messages.select.selected') }}</p>
                            </div>
                        </div>
                    </div>
                </v-touch>

                <v-touch v-else-if="!infoSidebar && !isASmallScreen"
                    class="__sidebar-swipe-hidden"
                    @swiperight="toggleInfoSidebar(), saveUserPref()"
                    @swipeleft="toggleInfoSidebar(), saveUserPref()">
                </v-touch>
            </section>

            {{-- ====================================================================== --}}

            {{-- path toolbar --}}
            <section class="__stack-breadcrumb level is-mobile">
                <div class="level-left">
                    {{-- directories breadCrumb --}}
                    <div class="level-item">
                        <nav class="breadcrumb has-arrow-separator is-hidden-touch">
                            <transition-group tag="ul" name="mm-list">
                                <li key="library-bc">
                                    <a v-if="pathBarDirsList.length > 0 && !(isBulkSelecting() || isLoading)"
                                        class="p-l-0 level"
                                        v-tippy="{arrow: true}"
                                        title="{{ trans('medialibrary::messages.backspace') }}"
                                        @click.stop="goToFolder(0)">
                                        <span class="icon level-item is-marginless"><icon name="map"></icon></span>
                                        <span class="level-item m-l-5 is-marginless">{{ trans('medialibrary::messages.library') }}</span>
                                    </a>
                                    <p v-else class="p-l-0 level">
                                        <span class="icon level-item is-marginless"><icon name="map-o"></icon></span>
                                        <span class="level-item m-l-5 is-marginless">{{ trans('medialibrary::messages.library') }}</span>
                                    </p>
                                </li>

                                <li v-for="(folder, index) in pathBarDirsList" :key="`${index}-${folder}`">
                                    <p v-if="isLastItemByIndex(index, pathBarDirsList) || isBulkSelecting() || isLoading"
                                        class="level">
                                        <span class="icon level-item is-marginless"><icon name="folder-open-o"></icon></span>
                                        <span class="level-item m-l-5 is-marginless">@{{ folder }}</span>
                                    </p>
                                    <a v-else
                                        v-tippy="{arrow: true}"
                                        title="{{ trans('medialibrary::messages.backspace') }}"
                                        class="level"
                                        @click.stop="pathBarDirsList.length > 1 ? goToFolder(index+1) : false">
                                        <span class="icon level-item is-marginless"><icon name="folder"></icon></span>
                                        <span class="level-item m-l-5 is-marginless">@{{ folder }}</span>
                                    </a>
                                </li>
                            </transition-group>
                        </nav>
                    </div>
                </div>

                <div class="level-right">
                     {{-- upload preview info --}}
                    <div class="level-item" v-if="waitingForUpload">
                        <nav class="breadcrumb">
                            <ul>
                                <li><p class="level has-text-weight-bold">@{{ uploadPreviewList.length }} File's</p></li>
                                <li><p class="level has-text-weight-bold">@{{ uploadPreviewListSize }}</p></li>
                            </ul>
                        </nav>
                    </div>

                    {{-- toggle sidebar --}}
                    <div class="level-item" v-show="!isLoading && !waitingForUpload">
                        <div class="is-hidden-touch"
                            @click.stop="toggleInfoSidebar(), saveUserPref()"
                            v-tippy="{arrow: true}"
                            title="t"
                            v-if="allItemsCount">
                            <transition :name="infoSidebar ? 'mm-info-out' : 'mm-info-in'" mode="out-in">
                                <div :key="infoSidebar ? 1 : 2" class="__stack-sidebar-toggle has-text-link">
                                    <template v-if="infoSidebar">
                                        <span>{{ trans('medialibrary::messages.close') }}</span>
                                        <span class="icon"><icon name="angle-double-right"></icon></span>
                                    </template>
                                    <template v-else>
                                        <span>{{ trans('medialibrary::messages.open') }}</span>
                                        <span class="icon"><icon name="angle-double-left"></icon></span>
                                    </template>
                                </div>
                            </transition>
                        </div>

                        {{-- show/hide toolbar --}}
                        <div class="is-hidden-desktop">
                            <button class="button is-link __stack-left-toolbarToggle" @click.stop="toolBar = !toolBar">
                                <span class="icon"><icon :name="toolBar ? 'times' : 'bars'"></icon></span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- ====================================================================== --}}

        {{-- modals --}}
        @include('medialibrary::partials.modal.ops')
    </div>
</media-library>

{{-- styles --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/MediaLibrary/css/library.css') }}"/>
@endpush

{{-- scripts --}}
@push('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/camanjs/4.1.2/caman.full.min.js"></script>
@endpush
