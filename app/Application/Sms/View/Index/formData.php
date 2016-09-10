<volist name="fields" id="item">
    <div class="form-group">
        <label for="{$item['field']}">{$item['name']}</label>
        <input type="text" name="{$item['field']}" value="{$item['value']}">
    </div>
</volist>