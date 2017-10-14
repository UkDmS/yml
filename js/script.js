    $(document).ready(function() {
        $('.addButton').on('click', function() {
            var index = $(this).data('index');
            if (!index) {
                index = 1;
                $(this).data('index', 1);
            }
            index++;
            $(this).data('index', index);

            var template     = $(this).attr('data-template'),
                $templateEle = $('#' + template + 'Template'),
                $row         = $templateEle.clone().removeAttr('id').insertBefore($templateEle).removeClass('hide'),
                $el          = $row.find('input').eq(0).attr('name', 'yml_settings[yml_currency][]');
                $el2          = $row.find('input').eq(1).attr('name', 'yml_settings[yml_rate][]');
            $('#defaultForm').bootstrapValidator('addField', $el);
            $('#defaultForm').bootstrapValidator('addField', $el2);

            $row.on('click', '.removeButton', function(e) {
                $('#defaultForm').bootstrapValidator('removeField', $el);
                $('#defaultForm').bootstrapValidator('removeField', $el2);
                $row.remove();
            });


        });
        $('#defaultForm').on('click','.remove', function(){
            var a = $(this).attr('number');
            $("div[number='" +a+ "']").remove();
        })

    });
