<div class="fileinput fileinput-new" data-provides="fileinput">
    <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;"> <img src="{$src}" alt=""> </div>
    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px; line-height: 10px;"> </div>
    <div>
        <span class="btn default btn-file">
            <span class="fileinput-new"> Select Photo </span>
            <span class="fileinput-exists"> Change</span>
            <input type="hidden">
            <input name="{$name}" id="{$name}" type="file"  {$input_attr}>
        </span>
        <a href="javascript:;" class="btn red fileinput-exists" data-dismiss="fileinput"> Delete </a> </div>
</div>
<div class="clearfix margin-top-10"><span class="label label-danger">Not!</span> IE10+, FF3.6+, Safari6.0+, Chrome6.0+ and Opera11.1+ </div>