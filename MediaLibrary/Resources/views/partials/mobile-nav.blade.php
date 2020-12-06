<transition-group tag="ul"
    class="__stack-breadcrumb-mobile is-hidden-desktop"
    ref="bc"
    v-if="pathBarDirsList.length > 0"
    name="mm-list">
    <li id="library-bc" key="library-bc">
        <a v-if="pathBarDirsList.length > 0 && !(isBulkSelecting() || isLoading)"
            v-tippy="{arrow: true}"
            title="{{ trans('medialibrary::messages.backspace') }}"
            @click.stop="goToFolder(0)">
            {{ trans('medialibrary::messages.library') }}
        </a>
        <p v-else>{{ trans('medialibrary::messages.library') }}</p>
    </li>

    <li v-for="(folder, index) in pathBarDirsList" :id="folder + '-bc'" :key="`${index}_${folder}`">
        <p v-if="isLastItemByIndex(index, pathBarDirsList) || isBulkSelecting() || isLoading">@{{ folder }}</p>
        <a v-else
            v-tippy="{arrow: true}"
            title="{{ trans('medialibrary::messages.backspace') }}"
            @click.stop="pathBarDirsList.length > 1 ? goToFolder(index+1) : false">
            @{{ folder }}
        </a>
    </li>
</transition-group>
