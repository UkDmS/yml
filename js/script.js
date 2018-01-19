    jQuery(document).ready(function() {
        jQuery('.addButton').on('click', function() {
            var index = jQuery(this).data('index');
            if (!index) {
                index = 1;
                jQuery(this).data('index', 1);
            }
            index++;
            jQuery(this).data('index', index);

            var template     = jQuery(this).attr('data-template'),
                jQuerytemplateEle = jQuery('#' + template + 'Template'),
                jQueryrow         = jQuerytemplateEle.clone().removeAttr('id').insertBefore(jQuerytemplateEle).removeClass('hide'),
                jQueryel          = jQueryrow.find('input').eq(0).attr('name', 'yml_settings[yml_currency][]');
                jQueryel2          = jQueryrow.find('input').eq(1).attr('name', 'yml_settings[yml_rate][]');
            jQuery('#defaultForm').bootstrapValidator('addField', jQueryel);
            jQuery('#defaultForm').bootstrapValidator('addField', jQueryel2);

            jQueryrow.on('click', '.removeButton', function(e) {
                jQuery('#defaultForm').bootstrapValidator('removeField', jQueryel);
                jQuery('#defaultForm').bootstrapValidator('removeField', jQueryel2);
                jQueryrow.remove();
            });


        });
        jQuery('#defaultForm').on('click','.remove', function(){
            var a = jQuery(this).attr('number');
            jQuery("div[number='" +a+ "']").remove();
        })

    });
