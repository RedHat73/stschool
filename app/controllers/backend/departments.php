<?php
use Tygh\Api;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\SiteArea;
use Tygh\Enum\UserTypes;
use Tygh\Enum\YesNo;
use Tygh\Registry;
use Tygh\Tools\Url;
use Tygh\Tygh;
use Tygh\Languages\Languages;

defined('BOOTSTRAP') or die('Access denied');

$auth = & Tygh::$app['session']['auth'];

$_REQUEST['department_id'] = empty($_REQUEST['department_id']) ? 0 : $_REQUEST['department_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    fn_trusted_vars(
        'departments', 
        'department_data',

    );
    $suffix = '';

        //
        // Delete departments
        //

        if ($mode == 'm_delete') {
            foreach ($_REQUEST['department_ids'] as $v) {
                fn_delete_department_by_id($v);
            }
            return array(CONTROLLER_STATUS_REDIRECT, 'departments.manage' . $_REQUEST['user_id']);
            $suffix = '.manage';
        }

    //     if (defined('AJAX_REQUEST')) {
    //         $redirect_url = fn_url('departments.manage');
    //         if (isset($_REQUEST['redirect_url'])) {
    //             $redirect_url = $_REQUEST['redirect_url'];
    //         }
    //         Tygh::$app['ajax']->assign('force_redirection', $redirect_url);
    //         Tygh::$app['ajax']->assign('non_ajax_notifications', true);
    //         return [CONTROLLER_STATUS_NO_CONTENT];
    //     }
    // 

    if ($mode == 'update') 
    {
        $department_id = !empty ($_REQUEST ['department_id']) ? $_REQUEST ['department_id'] : 0;
        $data = !empty ($_REQUEST['department_data']) ? $_REQUEST['department_data'] : []; 
        $department_id = fn_update_department($data, $department_id, $lang_code = CART_LANGUAGE);

        if (
            $mode === 'update'
            && isset($_REQUEST['department_ids'])
            && is_array($_REQUEST['department_ids'])
            && isset($_REQUEST['status'])
        ) {
            $status_to = (string) $_REQUEST['status'];
            foreach ($_REQUEST['department_ids'] as $department_id) 
            {
                fn_tools_update_status([
                    'table'             => 'departments',
                    'status'            => $status_to,
                    'id_name'           => 'department_id',
                    'id'                => $department_id,
                    'show_error_notice' => false
                ]);
            }
        }
        if (!empty($department_id)) 
        {
            $suffix = ".update?department_id = {'$department_id'}";
            return array(CONTROLLER_STATUS_REDIRECT, 'departments.update');
        }
    } elseif ($mode == 'delete') 
    {
        if (!empty($_REQUEST['department_id'])) {
            fn_delete_department_by_id($_REQUEST['department_id']);
            return array(CONTROLLER_STATUS_REDIRECT, 'departments.manage');
        }
    } elseif ($mode == 'm_delete') 
        {
            foreach ($_REQUEST['department_ids'] as $v) 
            {
                fn_delete_department_by_id($v);
            }
            return array(CONTROLLER_STATUS_REDIRECT, 'departments.manage');
        }
}

if ($mode === 'update') {

    $department_id = !empty($_REQUEST['department_id']) ? $_REQUEST['department_id'] : 0;
    $department_data = fn_get_department_data($department_id, CART_LANGUAGE);

    if($department_id = empty($_REQUEST['department_id'])) 
    {
        return array(CONTROLLER_STATUS_REDIRECT, 'departments.manage');
    }
    else $_REQUEST['department_id'];
    
    if (empty($department_data && $mode === 'update')) 
    {
        return [CONTROLLER_STATUS_NO_PAGE];
    }
    Tygh::$app['view']->assign([
        'department_data' => $department_data,
        'u_info' => !empty($department_data ['user_id']) ? fn_get_user_short_info($department_data ['user_id']) : []
    ]);
} elseif ($mode === 'manage' || $mode == 'picker'){

    list($departments, $search) = fn_get_departments($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'), CART_LANGUAGE);

    Tygh::$app['view']->assign('departments', $departments);
    Tygh::$app['view']->assign('search', $search);
}

function fn_get_department_data($department_id = 0, $lang_code = CART_LANGUAGE)
{
    $departments = [];
    if (!empty($department_id)) {
        list ($departments) = fn_get_departments([
            'department_id' => $department_id
        ], 1, $lang_code);

        if(!empty($departments)) {
            $departments = reset($departments);
            $departments['product_ids'] = fn_department_get_links ($departments['department_id']);
        }
    }
    return $departments;
}

function fn_get_departments($params = [], $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    // Set default values to input params
    $default_params = array(
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    if (AREA == 'C') {
        $params['status'] = 'A';
    }

    $sortings = array(
        'user_id' => '?:departments.user_id',
        'timestamp' => '?:departments.timestamp',
        'name' => '?:department_descriptions.department',
        'status' => '?:departments.status',
    );

    $condition = $limit = $join = '';

    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $sorting = db_sort($params, $sortings, 'name', 'asc');

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:departments.department_id IN (?n)', explode(',', $params['item_ids']));
    }

    if (!empty($params['department_id'])) {
            $condition .= db_quote(' AND ?:departments.department_id = ?i', $params['department_id']);
        }

    if (!empty($params['timestamp'])) {
        $condition .= db_quote(' AND ?:departments.timestamp = ?i', $params['timestamp']);
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(' AND ?:departments.status = ?s', $params['status']);
    }

    $fields = array (
        '?:departments.*',
        '?:department_descriptions.department',
        '?:department_descriptions.description',
        '?:department_images.department_image_id',
    );

    $join .= db_quote(' LEFT JOIN ?:department_descriptions ON ?:department_descriptions.department_id = ?:departments.department_id AND ?:department_descriptions.lang_code = ?s', $lang_code);
    $join .= db_quote(' LEFT JOIN ?:department_images ON ?:department_images.department_id = ?:departments.department_id AND ?:department_images.lang_code = ?s', $lang_code);

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:departments $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }

    $departments = db_get_hash_array(
        "SELECT ?p FROM ?:departments " .
        $join .
        "WHERE 1 ?p ?p ?p",
        'department_id', implode(', ', $fields), $condition, $sorting, $limit
    );
    
    if (!empty($params['item_ids'])) {
        $departments = fn_sort_by_ids($departments, explode(',', $params['item_ids']), 'department_id');
    }
    
    $department_image_ids = array_keys($departments);
    $images = fn_get_image_pairs($department_image_ids, 'department', 'M', true, false, $lang_code);

    foreach ($departments as $department_id => $department) {
        $departments[$department_id]['main_pair'] = !empty($images[$department_id]) ? reset($images[$department_id]) : array();
    }
    return array($departments, $params);
}

function fn_update_department($data, $department_id, $lang_code = CART_LANGUAGE)
{

    if (!empty($data['timestamp'])) {
        $data['timestamp'] = fn_parse_date($data['timestamp']);
    }

    if (!empty($department_id)) 
    {
        $pair_data = fn_attach_image_pairs('department', 'department', $department_image_id, $lang_code);

        db_query("UPDATE ?:departments SET ?u WHERE department_id = ?i", $data, $department_id);
        db_query("UPDATE ?:department_descriptions SET ?u WHERE department_id = ?i AND lang_code = ?s", $data, $department_id, $lang_code);
        db_query("UPDATE ?:department_images SET ?u WHERE department_id = ?i AND lang_code = ?s", $data, $department_id, $lang_code);

        $department_image_id = fn_get_department_image_id($department_id, $lang_code);
        $department_image_exist = !empty($department_image_id);
        $image_is_update = fn_departments_need_image_update();

        if (!$image_is_update && $department_image_exist) {
            fn_delete_image_pairs($department_image_id, 'department');
            db_query("DELETE FROM ?:department_images WHERE department_id = ?i AND lang_code = ?s", $department_id, $lang_code);
            $department_image_exist = false;
        }

        if ($image_is_update && !$department_image_exist) {
            $department_image_id = db_query("INSERT INTO ?:department_images (department_id, lang_code) VALUE(?i, ?s)", $department_id, $lang_code);
        }

    }
    else {
        $department_id = $data['department_id'] = db_query("REPLACE INTO ?:departments ?e", $data);
        foreach (Languages::getAll() as $data['lang_code'] => $v) 
        {
            db_query("REPLACE INTO ?:department_descriptions ?e", $data);
        }
        if (!empty($department_id)) 
        {
            $pair_data = fn_attach_image_pairs('department', 'department', $department_id, $lang_code);
            if (fn_departments_need_image_update()) 
            {
                $department_image_id = db_get_next_auto_increment_id('department_images');

                if (!empty($pair_data)) 
                {
                    $data_department_image = array(
                        'department_image_id' => $department_image_id,
                        'department_id'       => $department_id,
                        'lang_code'           => $lang_code
                    );
                    db_query("INSERT INTO ?:department_images ?e", $data_department_image);
                    fn_departments_image_all_links($department_id, $pair_data, $lang_code);
                }
            }
        }
    }

    $product_ids = !empty($data['product_ids']) ? $data ['product_ids'] : [];
    fn_department_delete_links($department_id);
    fn_department_add_links($department_id, $product_ids);

    return $department_id;
}

function fn_department_delete_links($department_id)
{
    db_query("DELETE FROM ?:department_links WHERE department_id = ?i", $department_id);
}

function fn_department_add_links($department_id, $product_ids)
{
    if (!empty($product_ids))
    {
        foreach ($product_ids as $product_id) 
        {
            db_query("REPLACE INTO ?:department_links ?e", [
                'product_id' => $product_id,
                'department_id' => $department_id,
            ]);
        }
    }
}

function fn_department_get_links($department_id)
{
    return isset($department_id) ? db_get_fields('SELECT product_id from ?:department_links WHERE department_id = ?i', $department_id) : [];
}

function fn_departments_need_image_update()
{
    if (!empty($_REQUEST['file_department_image_icon']) && is_array($_REQUEST['file_department_image_icon'])) 
    {
        $image_department = reset($_REQUEST['file_department_image_icon']);

        if ($image_department == 'department') 
        {
            return false;
        }
    }

    return true;
}

function fn_get_department_image_id($department_id, $lang_code = CART_LANGUAGE)
{
    return db_get_field("SELECT department_image_id FROM ?:department_images WHERE department_id = ?i AND lang_code = ?s", $department_id, $lang_code);
}

function fn_departments_image_all_links($department_id, $pair_data, $lang_code_list = CART_LANGUAGE)
{
    if (!empty($pair_data)) {

        $pair_id = reset($pair_data);

        // $lang_codes_list = Languages::getAll();
        // unset($lang_codes[$main_lang_code]);

        foreach ($lang_codes_list as $lang_code => $lang_data) {
            $_department_image_id = db_query("INSERT INTO ?:department_images (department_id, lang_code) VALUE(?i, ?s)", $department_id, $lang_code);
            fn_add_image_link($_department_image_id, $pair_id);
        }
    }
}

function fn_delete_department_by_id($department_id)
{
    if (!empty($department_id)) {
        db_query("DELETE FROM ?:departments WHERE department_id = ?i", $department_id);
        db_query("DELETE FROM ?:department_descriptions WHERE department_id = ?i", $department_id);
        db_query("DELETE FROM ?:department_images WHERE department_id = ?i", $department_id);
    }
}

function fn_get_department_statuses($status, $add_hidden = false, $lang_code = CART_LANGUAGE)
{
    $statuses = fn_get_default_statuses($status, $add_hidden, $lang_code);

    return $statuses;
}
?>


