<div id="product_features_{$block.block_id}">
<div class="ty-feature">
    {if $department_data.main_pair}
    <div class="ty-feature__image">
        {include file="common/image.tpl" 
        images=$department_data.main_pair
        image_width=$settings.Thumbnails.product_lists_thumbnail_width 
        image_height=$settings.Thumbnails.product_lists_thumbnail_height 
        }
    </div>
    {/if}
    <div class="ty-feature__description ty-wysiwyg-content">
        {$department_data.description nofilter}
    </div>

    {foreach from=$departments item=department}
        <div class="ty-feature__description ty-wysiwyg-content">
            {$department_data.product_ids nofilter}
        </div>
    {/foreach}
</div>

{capture name="mainbox_title"}{$department_data.department nofilter}{/capture}

{include file="common/pagination.tpl"}

<table class="ty-table ty-users-search">
    <thead>
        <tr>
            <th>{__("employees")}</th>
        </tr>
    </thead>
    {foreach from=$department_data item=employees}
        <tr>
            <td class="ty-users-search__item">
                <ul class="ty-users-search__user-info">
                    <li class="ty-users-search__user-name"><strong>{$employees.user_ids}</strong></li>
                </ul>
            </td>
        </tr>
    {foreachelse}
        <tr class="ty-table__no-items">
            <td colspan="7">
                <p class="ty-no-items">{__("text_no_employees")}</p>
            </td>
        </tr>
    {/foreach}
</table>

{include file="common/pagination.tpl"}

{capture name="mainbox_title"}{$department_data.department nofilter}{/capture}