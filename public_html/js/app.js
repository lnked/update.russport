(function(body){
    "use strict";

    new Clipboard('.j-clipboard', {
        text: function(trigger) {
            addClass(trigger, 'copied added');
            
            setTimeout(function(){
                removeClass(trigger, 'copied');
            }, 1000);

            return trigger.getAttribute('data-clipboard');
        }
    });

    var upload = document.querySelectorAll('.j-upload-input');

    upload.forEach(function(item) {
        item.addEventListener('change', function(e){
            console.log("change" + this.value);
        });
    });

    var change = {
        
        change: function($button) {
            var checked = false;

            if ($('#table').find('.j-change-item').filter(':checked').length) {
                checked = true;
            }
            
            $button.prop('disabled', !checked);
        },

        init: function() {
            var $table = $('#table'), $button = $('.j-change-button'), $checkbox = $table.find('.j-change-item');
            var _this = this;
            
            $table.find('.j-change-all').on('change', function() {
                $checkbox.prop('checked', $(this).prop('checked'));
                _this.change($button);
            });
            
            $table.find('.j-change-item').on('change', function() {
                _this.change($button);
            });

            _this.change($button);
        }

    };

    change.init();

})(document.body);