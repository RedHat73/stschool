{if $department_data}
    {assign var="id" value=$department_data.department_id}
{else}
    {assign var="id" value=0}
{/if}



{** departments section **}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit" name="departments_form" enctype="multipart/form-data">
<input type="hidden" class="cm-no-hide-input" name="department_id" value="{$id}" />

{capture name="tabsbox"}

    <div id="content_general">

        <div class="control-group">
            <label for="elm_department_name" class="control-label cm-required">{__("name")}</label>
            <div class="controls">
                <input type="text" name="department_data[department]" id="elm_department_name" value="{$department_data.department}" size="25" class="input-large" />
            </div>
        </div>

        <div class="control-group" id="department_data[department]">
            <label class="control-label">{__("image")}</label>
            <div class="controls">
                {include file="common/attach_images.tpl"
                    image_name="department"
                    image_object_type="department"
                    image_pair=$department_data.main_pair
                    image_object_id=$id
                    no_detailed=true
                    hide_titles=true
                }
            </div>
        </div>

        <div class="control-group id="department_text">
            <label class="control-label" for="elm_department_description">{__("description")}:</label>
            <div class="controls">
                <textarea id="elm_department_description" name="department_data[description]" cols="35" rows="8" class="cm-wysiwyg input-large">{$department_data.description}</textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label " for="elm_department_timestamp_{$id}">{__("creation_date")}</label>
            <div class="controls">
                {include file="common/calendar.tpl" date_id="elm_department_timestamp_`$id`" date_name="department_data[timestamp]" date_val=$department_data.timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
            </div>
        </div>

        {include file="common/select_status.tpl" input_name="department_data[status]" id="elm_department_status" obj_id=$id obj=$department_data hidden=true}

        <div class="control-group">
            <label class="control-label">{__("users")}</label>
            <div class="controls">
                {include 
                file="pickers/users/picker.tpl" 
                but_text=__("add_users") 
                data_id="return_users" 
                but_meta="btn" 
                input_name="department_data[user_id]" 
                item_ids=$department_data.users 
                placement="right"
                display="radio"
                view_mode="single_button"
                user_info=$u_info
                }
            </div>
        </div>
  
        {include 
            file="views/products/components/picker/picker.tpl"
            file="pickers/users/picker.tpl"
            but_text=__("add_users")
            data_id="return_users"
            but_meta="btn" 
            input_name="department_data[user_ids][]"
            item_ids=$department_data.user_ids
            placement="right"
            display="checkbox"
            multiple=true
            select_group_class="btn-toolbar"
        }
    <!--content_general--></div>

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}

{capture name="buttons"}
    {if !$id}
        {include file="buttons/save_cancel.tpl" but_role="submit-link" but_target_form="departments_form" but_name="dispatch[departments.update]"}
    {else}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[departments.update]" but_role="submit-link" but_target_form="departments_form" hide_first_button=$hide_first_button hide_second_button=$hide_second_button save=$id}
        {capture name="tools_list"}
            <li>{btn type="list" text=__("delete") class="cm-confirm" href="departments.delete?department_id=`$id`" method="POST"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}
{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="departments.add" prefix="top" hide_tools="true" title=__("add_department") icon="icon-plus"}
{/capture}
</form>

{/capture}

{include file="common/mainbox.tpl"
    title=($id) ? __("departments.change_department") : __("departments.new_department")
    content=$smarty.capture.mainbox
    buttons=$smarty.capture.buttons
    adv_buttons=$smarty.capture.adv_buttons 
    select_languages=false}

{** banner section **}
