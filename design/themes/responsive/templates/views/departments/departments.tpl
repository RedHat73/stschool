{if $departments}

    {script src="js/tygh/exceptions.js"}
    

    {if !$no_pagination}
        {include file="common/pagination.tpl"}
    {/if}

    {if !$show_empty}
        {split data=$departments size=$columns|default:"2" assign="splitted_departments"}
    {else}
        {split data=$departments size=$columns|default:"2" assign="splitted_departments" skip_complete=true}
    {/if}

    {math equation="100 / x" x=$columns|default:"2" assign="cell_width"}
    {if $item_number == "Y"}
        {assign var="cur_number" value=1}
    {/if}

    {* FIXME: Don't move this file *}
    {script src="js/tygh/product_image_gallery.js"}

    <div class="grid-list">
        {strip}
            {foreach from=$splitted_departments item="sdepartments"}
                {foreach from=$sdepartments item="department"}
                    <div class="ty-column3{$columns}">
                        {if $department}
                            {assign var="obj_id" value=$department.department_id}
                            {assign var="obj_id_prefix" value="`$obj_prefix``$department.department_id`"}
                            
                            <div class="ty-grid-list__item ty-quick-view-button__wrapper ty-grid-list__item--overlay">
                                <div class="ty-grid-list__image">
                                    <a href="{"departments.department?department_id={$department.department_id}"|fn_url}">
                                        {include 
                                            file="common/image.tpl" 
                                            images=$department.main_pair.icon|default:$department.main_pair.detailed
                                            image_id=$department.main_pair.image_id
                                            image_width=$settings.Thumbnails.product_lists_thumbnail_width 
                                            image_height=$settings.Thumbnails.product_lists_thumbnail_height 
                                            image_css_class="departments-list__image--img"
                                            link_css_class="departments-list__image--link"

                                        }
                                    </a>
                                </div>

                                <div class="ty-compact-list__title">
                                    <bdi>
                                        <a href="{"departments.department?department_id={$department.department_id}"|fn_url}" class="product-title" title="{$department.department}">{$department.department}</a>    
                                    </bdi>
                                    <div class="ty-control-group ty-sku-item cm-hidden-wrapper" id="sku_update_227">
                                        <span class="ty-control-group__item cm-reload-227" id="{$department.user_id}">{$department.user_id}</span>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    </div>
                {/foreach}
            {/foreach}
        {/strip}
    </div>

    {if !$no_pagination}
        {include file="common/pagination.tpl"}
    {/if}

{/if}

{capture name="mainbox_title"}{$title}{/capture}