<div id = "simpleTabs-content-3" class = "simpleTabs-content">
    <table class = 'form-table'>
        <tr> 
            <th scope="row"><?php _e('Preferred Text Type', 'postie') ?> </th> 
            <td>
                <select name='postie-settings[prefer_text_type]' id='postie-settings-prefer_text_type'>
                    <?php printf('<option value="plain" %s>plain</option>', ($prefer_text_type == "plain") ? "selected" : "") ?>
                    <?php printf('<option value="html" %s>html</option>', ($prefer_text_type == "html") ? "selected" : "") ?>
                </select>
            </td> 
        </tr> 
        <tr valign = "top">
            <th scope = "row"><?php _e('Default category', 'postie') ?></th>
            <td>
                <?php
                $defaultCat = $default_post_category;
                $args = array('name' => 'postie-settings[default_post_category]', 'hierarchical' => 1, 'selected' => $defaultCat, 'hide_empty' => 0);
                wp_dropdown_categories($args);
                ?>
        </tr>
        <?php
        echo BuildBooleanSelect(__("Match short category", 'postie'), "postie-settings[category_match]", $category_match, __("Try to match categories using 'starts with logic' otherwise only do exact matches.<br />Note that custom taxonomies will not be found if this setting is 'No'", 'postie'));

        echo BuildBooleanSelect(__("Use colon to match category", 'postie'), "postie-settings[category_colon]", $category_colon);
        echo BuildBooleanSelect(__("Use dash to match category", 'postie'), "postie-settings[category_dash]", $category_dash);
        echo BuildBooleanSelect(__("Use square bracket to match category", 'postie'), "postie-settings[category_bracket]", $category_bracket, __('See the following article for more information <a href="http://postieplugin.com/faq/override-post-categories/" target="_blank">http://postieplugin.com/faq/override-post-categories/</a>', 'postie'));
        ?>

        <tr valign="top">
            <th scope="row">
                <?php _e('Default tag(s)', 'postie') ?><br />
            </th>
            <td>
                <input type='text' name='postie-settings[default_post_tags]' id='postie-settings-default_post_tags' value='<?php echo esc_attr($default_post_tags) ?>' />
                <p class='description'><?php _e('separated by commas', 'postie') ?></p>
            </td>
        </tr>

        <tr> 
            <th scope="row"><?php _e('Default Post Status', 'postie') ?> </th> 
            <td>
                <select name='postie-settings[post_status]' id='postie-settings-post_status'>                               
                    <?php
                    $stati = get_post_stati();
                    //DebugEcho($config['post_status']);
                    //DebugDump($stati);
                    foreach ($stati as $status) {
                        $selected = "";
                        if ($config['post_status'] == $status) {
                            $selected = " selected='selected'";
                        }
                        echo "<option value='$status'$selected>$status</option>";
                    }
                    ?>
                </select>               
            </td> 
        </tr> 

        <tr> 
            <th scope="row"><?php _e('Default Post Format', 'postie') ?> </th> 
            <td>
                <select name='postie-settings[post_format]' id='postie-settings-post_format'>
                    <?php
                    $formats = get_theme_support('post-formats');
                    if (is_array($formats[0])) {
                        $formats = $formats[0];
                    } else {
                        $formats = array();
                    }
                    array_unshift($formats, "standard");
                    foreach ($formats as $format) {
                        $selected = "";
                        if ($config['post_format'] == $format) {
                            $selected = " selected='selected'";
                        }
                        echo "<option value='$format'$selected>$format</option>";
                    }
                    ?>
                </select>               
            </td> 
        </tr> 

        <tr> 
            <th scope="row"><?php _e('Default Post Type', 'postie') ?> </th> 
            <td>
                <select name='postie-settings[post_type]' id='postie-settings-post_type'>
                    <?php
                    $types = get_post_types();
                    //array_unshift($types, "standard");
                    foreach ($types as $type) {
                        $selected = "";
                        if ($config['post_type'] == $type) {
                            $selected = " selected='selected'";
                        }
                        echo "<option value='$type'$selected>$type</option>";
                    }
                    ?>
                </select>               
            </td> 
        </tr> 

        <tr> 
            <th scope="row"><?php _e('Default Title', 'postie') ?> </th> 
            <td>
                <input name='postie-settings[default_title]' type="text" id='postie-settings-default_title' value="<?php echo esc_attr($default_title); ?>" size="50" /><br />
            </td> 
        </tr> 

        <?php echo BuildBooleanSelect(__("Treat Replies As", 'postie'), "postie-settings[reply_as_comment]", $reply_as_comment, "", array("comments", "new posts")); ?>
        <?php echo BuildBooleanSelect(__("Strip Original Content from Replies", 'postie'), "postie-settings[strip_reply]", $strip_reply, "Only applicable if replies are trated as comments"); ?>

        <?php echo BuildBooleanSelect(__("Forward Rejected Mail", 'postie'), "postie-settings[forward_rejected_mail]", $forward_rejected_mail); ?>
        <?php echo BuildBooleanSelect(__("Allow Subject In Mail", 'postie'), "postie-settings[allow_subject_in_mail]", $allow_subject_in_mail, "Enclose the subject between '#' on the very first line. E.g. #this is my subject#"); ?>
        <?php echo BuildBooleanSelect(__("Allow HTML In Mail Subject", 'postie'), "postie-settings[allow_html_in_subject]", $allow_html_in_subject); ?>
        <?php echo BuildBooleanSelect(__("Allow HTML In Mail Body", 'postie'), "postie-settings[allow_html_in_body]", $allow_html_in_body); ?>
        <tr> 
            <th scope="row"><?php _e('Text for Message Start', 'postie') ?> </th>
            <td>
                <input name='postie-settings[message_start]' type="text" id='postie-settings-message_start' value="<?php echo esc_attr($message_start); ?>" size="50" /><br />
                <p class='description'><?php _e('Remove all text from the beginning of the message up to the point where this is found. Note this works best with "Plain" messages.', 'postie') ?></p>
            </td> 
        </tr>
        <tr>
            <th scope="row"><?php _e('Text for Message End', 'postie') ?> </th>
            <td>
                <input name='postie-settings[message_end]' type="text" id='postie-settings-message_end' value="<?php echo esc_attr($message_end); ?>" size="50" /><br />
                <p class='description'><?php _e('Remove all text from the point this is found to the end of the message. Note this works best with "Plain" messages.', 'postie') ?></p>
            </td>
        </tr>

        <?php
        echo BuildBooleanSelect(__("Filter newlines", 'postie'), "postie-settings[filternewlines]", $filternewlines, __("Whether to strip newlines from plain text. Set to no if using markdown or textitle syntax", 'postie'));
        echo BuildBooleanSelect(__("Replace newline characters with html line breaks (&lt;br /&gt;)", 'postie'), "postie-settings[convertnewline]", $convertnewline, __("Filter newlines must be turned on for this option to take effect", 'postie'));
        echo BuildBooleanSelect(__("Return rejected mail to sender", 'postie'), "postie-settings[return_to_sender]", $return_to_sender);
        ?>
        <tr>
            <th>
                <?php _e("Send post confirmation email to", 'postie') ?>:
            </th>
            <td>
                <select name='postie-settings[confirmation_email]' id='postie-settings-confirmation_email'>
                    <option value="sender" <?php echo($confirmation_email == "sender") ? "selected" : "" ?>><?php _e('sender', 'postie') ?></option>
                    <option value="admin" <?php echo ($confirmation_email == "admin") ? "selected" : "" ?>><?php _e('administrator', 'postie') ?></option>
                    <option value="both" <?php echo ($confirmation_email == "both") ? "selected" : "" ?>><?php _e('sender and administrator', 'postie') ?></option>
                    <option value="" <?php echo ($confirmation_email == "") ? "selected" : "" ?>><?php _e('none', 'postie') ?></option>
                </select>
            </td>
        </tr>

        <?php
        echo BuildBooleanSelect(__("Automatically convert urls to links", 'postie'), "postie-settings[converturls]", $converturls);
        ?>
        <tr> 
            <th scope="row"><?php _e('Encoding for pages and feeds', 'postie') ?> </th> 
            <td>
                <input name='postie-settings[message_encoding]' type="text" id='postie-settings-message_encoding' value="<?php echo esc_attr($message_encoding); ?>" size="10" />
                <p class='description'><?php _e('The character set for your blog.', 'postie') ?></p>
                <p class='description'>UTF-8 <?php _e("should handle ISO-8859-1 as well", 'postie'); ?></p>
            </td> 
        </tr> 
        <?php echo BuildBooleanSelect(__("Decode Quoted Printable Data", 'postie'), "postie-settings[message_dequote]", $message_dequote); ?>
        <?php echo BuildBooleanSelect(__("Drop The Signature From Mail", 'postie'), "postie-settings[drop_signature]", $drop_signature, __("Really only works with 'plain' format.")); ?>
        <?php echo BuildTextArea(__("Signature Patterns", 'postie'), "postie-settings[sig_pattern_list]", $sig_pattern_list, __("Really only works with 'plain' format. Put each pattern on a separate line. Patterns are <a href='http://regex101.com/' target='_blank'>regular expressions</a> and are put inside /^{pattern}$/i", 'postie')); ?>
    </table> 
</div>