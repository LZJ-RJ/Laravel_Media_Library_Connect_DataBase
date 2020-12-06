{{-- library --}}
<div class="modal mm-animated fadeIn is-active modal-library__Inmodal">
    <div class="modal-background" @click.stop="hideInputModal()"></div>
    <div class="modal-content mm-animated fadeInDown">
        <div class="box">
            @include('MediaLibrary::_library', ['modal' => true])
        </div>
    </div>
    <button class="modal-close is-large is-hidden-touch" @click.stop="hideInputModal()"></button>
</div>
