<!-- Modal -->
<div class="modal fade" id="{{isset($modalName)?$modalName:'modalName'}}" tabindex="-1" role="dialog" aria-labelledby="{{isset($modalName)?$modalName:'modalName'}}Label" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width:80%;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="{{isset($modalName)?$modalName:'modalName'}}"><strong>{{isset($titleText)?$titleText:'titleText'}}</strong></h2>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('tm.back')}}</button>
                <button type="button" class="btn btn-success modalSelectButton" data-dismiss="modal">{{trans('tm.select')}}</button>
            </div>
        </div>
    </div>
</div>
