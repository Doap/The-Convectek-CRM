<div id="simpleTabs-content-1" class="simpleTabs-content">

    <table class='form-table'>

        <tr>
            <th scope="row"><lable for="postie-settings-input_protocol"><?php _e('Mail Protocol', 'postie') ?></lable></th>
        <td>
            <select name='postie-settings[input_protocol]' id='postie-settings-input_protocol'>
                <option value="pop3"  <?php echo (($input_protocol == "pop3") ? " selected='selected' " : "") ?>>POP3</option>
                <?php if (HasIMAPSupport(false)): ?>
                    <option value="imap" <?php echo ($input_protocol == "imap") ? "selected='selected' " : "" ?>>IMAP</option>
                    <option value="pop3-ssl" <?php echo ($input_protocol == "pop3-ssl") ? "selected='selected' " : "" ?>>POP3-SSL</option>
                    <option value="imap-ssl" <?php echo ($input_protocol == "imap-ssl") ? "selected='selected' " : "" ?>>IMAP-SSL</option>
                <?php endif; ?>
            </select>
            <?php if (!HasIMAPSupport(false)): ?>
                <span class="recommendation">IMAP/IMAP-SSL/POP3-SSL unavailable</span>
            <?php endif; ?>
        </td>
        </tr>

        <?php echo BuildBooleanSelect(__("Use Transport Layer Security (TLS)", 'postie'), 'postie-settings[email_tls]', $email_tls, __("Choose Yes if your server requires TLS", 'postie')); ?>

        <tr>
            <th scope="row"><label for="postie-settings-mail_server_port"><?php _e('Port', 'postie') ?></label></th>
            <td valign="top">
                <input name='postie-settings[mail_server_port]' style="width: 70px;" type="number" min="0" id='postie-settings-mail_server_port' value="<?php echo esc_attr($mail_server_port); ?>" size="6" />
                <p class='description'><?php _e("Standard Ports:", 'postie'); ?><br />
                    <?php _e("POP3", 'postie'); ?> - 110<br />
                    <?php _e("IMAP", 'postie'); ?> - 143<br />
                    <?php _e("IMAP-SSL", 'postie'); ?>- 993 <br />
                    <?php _e("POP3-SSL", 'postie'); ?> - 995 <br />
                </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Mail Server', 'postie') ?></th>
            <td><input name='postie-settings[mail_server]' type="text" id='postie-settings-mail_server' value="<?php echo esc_attr($mail_server); ?>" size="40" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Mail Userid', 'postie') ?></th>
            <td><input name='postie-settings[mail_userid]' type="text" id='postie-settings-mail_userid' autocomplete='off' value="<?php echo esc_attr($mail_userid); ?>" size="40" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Mail Password', 'postie') ?></th>
            <td>
                <input name='postie-settings[mail_password]' type="password" id='postie-settings-mail_password' autocomplete='off' value="<?php echo esc_attr($mail_password); ?>" size="40" />
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Postie Time Correction', 'postie') ?></th>
            <td><input style="width: 70px;" name='postie-settings[time_offset]' type="number" step="0.5" id='postie-settings-time_offset' size="2" value="<?php echo esc_attr($time_offset); ?>" /> 
                <?php _e('hours', 'postie') ?> 
                <p class='description'><?php _e("Should be the same as your normal offset, but this lets you adjust it in cases where that doesn't work.", 'postie'); ?></p>
            </td>
        </tr>
        <tr>
            <th>
                <?php _e('Check for mail every', 'postie') ?>:
            </th>
            <td>
                <select name='postie-settings[interval]' id='postie-settings-interval'>
                    <option value="weekly" <?php
                    if ($interval == "weekly") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('Once weekly', 'postie') ?>
                    </option>

                    <option value="daily"<?php
                    if ($interval == "daily") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('daily', 'postie') ?>
                    </option>

                    <option value="hourly" <?php
                    if ($interval == "hourly") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('hourly', 'postie') ?>
                    </option>

                    <option value="twiceperhour" <?php
                    if ($interval == "twiceperhour") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('twice per hour', 'postie') ?>
                    </option>

                    <option value="tenminutes" <?php
                    if ($interval == "tenminutes") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 10 minutes', 'postie') ?>
                    </option>

                    <option value="fiveminutes" <?php
                    if ($interval == "fiveminutes") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 5 minutes', 'postie') ?>
                    </option>

                    <option value="oneminute" <?php
                    if ($interval == "oneminute") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 1 minute', 'postie') ?>
                    </option>

                    <option value="thirtyseconds" <?php
                    if ($interval == "thirtyseconds") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 30 seconds', 'postie') ?>
                    </option>

                    <option value="fifteenseconds" <?php
                    if ($interval == "fifteenseconds") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 15 seconds', 'postie') ?>
                    </option>

                    <option value="manual" <?php
                    if ($interval == "manual") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('check manually', 'postie') ?>
                    </option>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <?php _e('Maximum number of emails to process', 'postie'); ?>
            </th>
            <td>
                <select name='postie-settings[maxemails]' id='postie-settings-maxemails'>
                    <option value="0" <?php if ($maxemails == '0') echo "selected='selected'" ?>><?php _e('All', 'postie'); ?></option>
                    <option value="1" <?php if ($maxemails == '1') echo "selected='selected'" ?>>1</option>
                    <option value="2" <?php if ($maxemails == '2') echo "selected='selected'" ?>>2</option>
                    <option value="5" <?php if ($maxemails == '5') echo "selected='selected'" ?>>5</option>
                    <option value="10" <?php if ($maxemails == '10') echo "selected='selected'" ?>>10</option>
                    <option value="25" <?php if ($maxemails == '25') echo "selected='selected'" ?>>25</option>
                    <option value="50" <?php if ($maxemails == '50') echo "selected='selected'" ?>>50</option>
                </select>
            </td>
        </tr>
        <?php echo BuildBooleanSelect(__("Delete email after posting", 'postie'), 'postie-settings[delete_mail_after_processing]', $delete_mail_after_processing, __("Only set to no for testing purposes", 'postie')); ?>
        <?php echo BuildBooleanSelect(__("Ignore mail state", 'postie'), 'postie-settings[ignore_mail_state]', $ignore_mail_state, __("Ignore whether the mails is 'read' or 'unread' If 'No' then only unread messages are processed.", 'postie')); ?>

        <?php echo BuildBooleanSelect(__("Enable Error Logging", 'postie'), 'postie-settings[postie_log_error]', $postie_log_error, __("Log error messages to the web server error log.", 'postie')); ?>
        <?php echo BuildBooleanSelect(__("Enable Debug Logging", 'postie'), 'postie-settings[postie_log_debug]', $postie_log_debug, __("Log debug messages to the web server error log.", 'postie')); ?>

    </table>
</div>