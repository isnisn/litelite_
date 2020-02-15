<?php

  if (!empty($_GET['language_code'])) {
    $language = new ent_language($_GET['language_code']);
  } else {
    $language = new ent_language();
  }

  if (empty($_POST)) {
    foreach ($language->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($language->data['id']) ? language::translate('title_edit_language', 'Edit Language') : language::translate('title_add_new_language', 'Add New Language'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['code'])) throw new Exception(language::translate('error_must_enter_code', 'You must enter a code'));
      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));

      if (!empty($_POST['code']) && empty($language->data['id'])) {
        $languages_query = database::query(
          "select id from ". DB_TABLE_LANGUAGES ."
          where code = '". database::input($_POST['code']) ."'
          limit 1;"
        );

        if (database::num_rows($languages_query)) {
          throw new Exception(language::translate('error_language_already_exists', 'The language already exists in the database'));
        }
      }

      if (!empty($_POST['code']) && !empty($language->data['id']) && $language->data['code'] != $_POST['code']) {
        if ($language->data['code'] == 'en') {
          throw new Exception(language::translate('error_cannot_rename_framework_language', 'You cannot not rename the framework language, but you can disable it'));
        }

        $languages_query = database::query(
          "select id from ". DB_TABLE_LANGUAGES ."
          where code = '". database::input($_POST['code']) ."'
          limit 1;"
        );

        if (database::num_rows($languages_query)) {
          throw new Exception(language::translate('error_language_already_exists', 'The language already exists in the database'));
        }
      }

      if (empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('default_language_code')) {
        throw new Exception(language::translate('error_cannot_disable_default_language', 'You must change the default language before disabling it.'));
      }

      if (empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('store_language_code')) {
        throw new Exception(language::translate('error_cannot_disable_store_language', 'You must change the store language before disabling it.'));
      }

      if (empty($_POST['set_default']) && isset($language->data['code']) && $language->data['code'] == settings::get('default_language_code') && $language->data['code'] != $_POST['code']) {
        throw new Exception(language::translate('error_cannot_rename_default_language', 'You must change the default language before renaming it.'));
      }

      if (empty($_POST['set_store']) && isset($language->data['code']) && $language->data['code'] == settings::get('store_language_code') && $language->data['code'] != $_POST['code']) {
        throw new Exception(language::translate('error_cannot_rename_store_language', 'You must change the store language before renaming it.'));
      }

      if (!empty($_POST['set_default']) && empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('default_language_code')) {
        throw new Exception(language::translate('error_cannot_set_disabled_default_language', 'You cannot set a disabled language as default language.'));
      }

      if (!empty($_POST['set_store']) && empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('store_language_code')) {
        throw new Exception(language::translate('error_cannot_set_disabled_store_language', 'You cannot set a disabled language as store language.'));
      }

      if (!preg_grep('#'. preg_quote($_POST['charset'], '#') .'#i', mb_list_encodings())) {
        throw new Exception(strtr(language::translate('error_not_a_supported_charset', '%charset is not a supported character set'), array('%charset' => !empty($_POST['charset']) ? $_POST['charset'] : 'NULL')));
      }

      if (!setlocale(LC_ALL,  explode(',', $_POST['locale']))) {
        throw new Exception(strtr(language::translate('error_not_a_valid_system_locale', '%locale is not a valid system locale on this machine'), array('%locale' => !empty($_POST['locale']) ? $_POST['locale'] : 'NULL')));
      }
      setlocale(LC_ALL, explode(',', language::$selected['locale']));

      ##########

      $_POST['code'] = strtolower($_POST['code']);
      $_POST['raw_datetime'] = $_POST['raw_date'] .' '. $_POST['raw_time'];
      $_POST['format_datetime'] = $_POST['format_date'] .' '. $_POST['format_time'];

      $fields = array(
        'status',
        'code',
        'code2',
        'name',
        'charset',
        'locale',
        'raw_date',
        'raw_time',
        'raw_datetime',
        'format_date',
        'format_time',
        'format_datetime',
        'decimal_point',
        'thousands_sep',
        'currency_code',
        'priority',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $language->data[$field] = $_POST[$field];
      }

      $language->save();

      if (!empty($_POST['set_default'])) {
        database::query("update ". DB_TABLE_SETTINGS ." set `value` = '". database::input($_POST['code']) ."' where `key` = 'default_language_code' limit 1;");
      }

      if (!empty($_POST['set_store'])) {
        database::query("update ". DB_TABLE_SETTINGS ." set `value` = '". database::input($_POST['code']) ."' where `key` = 'store_language_code' limit 1;");
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, array('doc' => 'languages'), true, array('action', 'language_code')));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($language->data['id'])) throw new Exception(language::translate('error_must_provide_language', 'You must provide a language'));

      if ($language->data['code'] == 'en') {
        throw new Exception(language::translate('error_cannot_delete_framework_language', 'You cannot delete the PHP framework language. But you can disable it.'));
      }

      if ($language->data['code'] == settings::get('default_language_code')) {
        throw new Exception(language::translate('error_cannot_delete_default_language', 'You must change the default language before it can be deleted.'));
      }

      if ($language->data['code'] == settings::get('store_language_code')) {
        throw new Exception(language::translate('error_cannot_delete_store_language', 'You must change the store language before it can be deleted.'));
      }

      $language->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, array('doc' => 'languages'), true, array('action', 'language_code')));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo !empty($language->data['id']) ? language::translate('title_edit_language', 'Edit Language') : language::translate('title_add_new_language', 'Add New Language'); ?>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('language_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_status', 'Status'); ?></label>
          <div class="btn-group btn-block btn-group-inline" data-toggle="buttons">
            <label class="btn btn-default<?php echo ($_POST['status'] == 1) ? ' active' : ''; ?>"><?php echo functions::form_draw_radio_button('status', '1', true); ?> <?php echo language::translate('title_enabled', 'Enabled'); ?></label>
            <label class="btn btn-default<?php echo ($_POST['status'] == -1) ? ' active' : ''; ?>"><?php echo functions::form_draw_radio_button('status', '-1', true); ?><?php echo language::translate('title_hidden', 'Hidden'); ?></label>
            <label class="btn btn-default<?php echo ($_POST['status'] == 0) ? ' active' : ''; ?>"><?php echo functions::form_draw_radio_button('status', '0', true); ?><?php echo language::translate('title_disabled', 'Disabled'); ?></label>
          </div>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
            <?php echo functions::form_draw_text_field('name', true); ?>
        </div>

      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_code', 'Code'); ?> (ISO 639-1) <a href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
            <?php echo functions::form_draw_text_field('code', true, 'required="required" pattern="[a-z]{2}"'); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_code', 'Code'); ?> 2 (ISO 639-2) <a href="http://en.wikipedia.org/wiki/List_of_ISO_639-2_codes" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
            <?php echo functions::form_draw_text_field('code2', true, 'required="required" pattern="[a-z]{3}"'); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
            <?php echo functions::form_draw_text_field('charset', (file_get_contents('php://input') == '') ? 'UTF-8' : true, 'required="required" placeholder="UTF-8"'); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_system_locale', 'System Locale'); ?></label>
            <?php echo functions::form_draw_text_field('locale', true, 'placeholder="E.g. en_US.utf8,en-US.UTF-8,english"'); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_date_format', 'Date Format'); ?> <a href="http://php.net/manual/en/function.strftime.php" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
<?php
  $options = array(
    array(language::strftime('%e %b %Y'), '%e %b %Y'),
    array(language::strftime('%b %e %Y'), '%b %e %Y'),
  );
  echo functions::form_draw_select_field('format_date', $options, true);
?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_time_format', 'Time Format'); ?> <a href="http://php.net/manual/en/function.strftime.php" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
<?php
  $options = array(
    array(
      'label' => '12-Hour Format',
      'options' => array(
        array(language::strftime('%I:%M %p'), '%I:%M %P'),
      ),
    ),
    array(
      'label' => '24-Hour Format',
      'options' => array(
        array(language::strftime('%H:%M'), '%H:%M'),
      ),
    ),
  );
  echo functions::form_draw_select_optgroup_field('format_time', $options, true, false);
?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_raw_date_format', 'Raw Date Format'); ?> <a href="http://php.net/manual/en/function.date.php" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
<?php
  $options = array(
    array(
      'label' => 'Big-endian (YMD)', 'null', 'style="font-weight: bold;" disabled="disabled"',
      'options' => array(
        array(date('Y-m-d'), 'Y-m-d'),
        array(date('Y.m.d'), 'Y.m.d'),
        array(date('Y/m/d'), 'Y/m/d'),
      ),
    ),
    array(
      'label' => 'Little-endian (DMY)', 'null', 'style="font-weight: bold;" disabled="disabled"',
      'options' => array(
        array(date('d-m-Y'), 'd-m-Y'),
        array(date('d.m.Y'), 'd.m.Y'),
        array(date('d/m/Y'), 'd/m/Y'),
      ),
    ),
    array(
      'label' => 'Middle-endian (MDY)', 'null', 'style="font-weight: bold;" disabled="disabled"',
      'options' => array(
        array(date('m/d/y'), 'm/d/y'),
      ),
    ),
  );
  echo functions::form_draw_select_optgroup_field('raw_date', $options, true, false);
?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_raw_time_format', 'Raw Time Format'); ?> <a href="http://php.net/manual/en/function.date.php" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
<?php
  $options = array(
    array(
      'label' => '12-hour format',
      'options' => array(
        array(date('h:i A'), 'h:i A'),
      ),
    ),
    array(
      'label' => '24-hour format',
      'options' => array(
        array(date('H:i'), 'H:i'),
      )
    ),
  );
  echo functions::form_draw_select_optgroup_field('raw_time', $options, true, false);
?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_decimal_point', 'Decimal Point'); ?></label>
<?php
  $options = array(
    array(language::translate('char_dot', 'Dot'), '.'),
    array(language::translate('char_comma', 'Comma'), ','),
  );
  echo functions::form_draw_select_field('decimal_point', $options, true);
?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_thousands_sep', 'Thousands Separator'); ?></label>
<?php
  $options = array(
    array(language::translate('char_comma', 'Comma'), ','),
    array(language::translate('char_dot', 'Dot'), '.'),
    array(language::translate('char_space', 'Space'), ' '),
    array(language::translate('char_nonbreaking_space', 'Non-Breaking Space'), ' '),
    array(language::translate('char_single_quote', 'Single quote'), '\''),
  );
  echo functions::form_draw_select_field('thousands_sep', $options, true);
?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_force_currency_code', 'Force Currency Code'); ?></label>
          <?php echo functions::form_draw_text_field('currency_code', true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
          <?php echo functions::form_draw_number_field('priority', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <div class="radio">
            <label><?php echo functions::form_draw_checkbox('set_default', '1', (isset($language->data['code']) && $language->data['code'] && $language->data['code'] == settings::get('default_language_code')) ? '1' : true); ?> <?php echo language::translate('description_set_as_default_language', 'Set as default language'); ?></label>
          </div>
          <div class="radio">
            <label><?php echo functions::form_draw_checkbox('set_store', '1', (isset($language->data['code']) && $language->data['code'] && $language->data['code'] == settings::get('store_language_code')) ? '1' : true); ?> <?php echo language::translate('description_set_as_store_language', 'Set as store language'); ?></label>
          </div>
        </div>
      </div>

      <div class="panel-action btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($language->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
