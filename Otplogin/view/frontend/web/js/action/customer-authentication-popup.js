define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Customer/js/customer-data',
    'mage/storage',
    'mage/translate',
    'mage/mage',
    'jquery/ui'
], function ($, modal, customerData, storage, $t) {
    'use strict';

    $.widget('cinovic.customerAuthenticationPopup', {
        options: {
            login: '#customer-popup-login',
            nextRegister: '#customer-popup-registration',
            register: '#customer-popup-register',
            prevLogin: '#customer-popup-sign-in',
            otp: '#customer-popup-otp'
        },

        /**
         *
         * @private
         */
        _create: function () {
            var self = this,
                authentication_options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: this.options.popupTitle,
                    buttons: false,
                    modalClass : 'customer-popup'
                };

            modal(authentication_options, this.element);

            // Show the login form in a popup when clicking on the sign in text
            $('body').on('click', '.customer-login-link, '+self.options.prevLogin, function() {
                $('.modal-title').css('display','none');
                $(self.options.register).modal('closeModal');
                $(self.options.otp).modal('closeModal');
                $(self.options.login).modal('openModal');
                return false;
            });

            // Show the registration form in a popup when clicking on the create an account text
            $('body').on('click', '.customer-register-link, '+self.options.nextRegister, function() {
                $('.modal-title').css('display','block');
                $(self.options.otp).modal('closeModal');
                $(self.options.login).modal('closeModal');
                $(self.options.register).modal('openModal');
                return false;
            });

            this._ajaxSubmit();
        },
      

        /**
         * Submit data by Ajax
         * @private
         */
        _ajaxSubmit: function() {
            var self = this,
                form = this.element.find('form'),
                inputElement = form.find('input');

            inputElement.keyup(function (e) {
                self.element.find('.messages').html('');
            });

            form.submit(function (e) {
                if (form.validation('isValid')) {
                    if (form.hasClass('form-create-account')) {
                        $.ajax({
                            url: $(e.target).attr('action'),
                            data: $(e.target).serializeArray(),
                            showLoader: true,
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {                                                            
                                $(self.options.register).modal('closeModal');
                                $(self.options.otp).modal('openModal');   
                                $('<div class="message message-success success"><div>' + response.message + '</div></div>').appendTo('.otpmessages');             
                            },
                            error: function() {
                                $('<div class="message message-error error"><div>' + response.message + '</div></div>').appendTo('.messages');
                            }
                        });
                    } else {
                        var submitData = {},
                            formDataArray = $(e.target).serializeArray();
                        formDataArray.forEach(function (entry) {
                            submitData[entry.name] = entry.value;
                        });
                        $('body').loader().loader('show');
                        storage.post(
                            $(e.target).attr('action'),
                            JSON.stringify(submitData)
                        ).done(function (response) {
                            $('body').loader().loader('hide');
                            self._showResponse(response, form.find('input[name="redirect_url"]').val());
                        }).fail(function () {
                            $('body').loader().loader('hide');
                            self._showFailingMessage();
                        });
                    }
                }
                return false;
            });
        },

        /**
         * Display messages on the screen
         * @private
         */
        _displayMessages: function(className, message) {
            $('<div class="message '+className+'"><div>'+message+'</div></div>').appendTo(this.element.find('.messages'));
        },

        /**
         * Showing response results
         * @private
         * @param {Object} response
         * @param {String} locationHref
         */
        _showResponse: function(response) {
            var self = this,
                timeout = 800;
            this.element.find('.messages').html('');
            if (response.errors) {
                this._displayMessages('message-error error', response.message);
            } else {
                this._displayMessages('message-success success', response.message);
            }
            this.element.find('.messages .message').show();
            setTimeout(function() {
                if (!response.errors) {
                    self.element.modal('closeModal');
                }
            }, timeout);
        },

        /**
         * Show the failing message
         * @private
         */
        _showFailingMessage: function() {
            this.element.find('.messages').html('');
            this._displayMessages('message-error error', $t('An error occurred, please try again later.'));
            this.element.find('.messages .message').show();
        }
    });

    return $.cinovic.customerAuthenticationPopup;
});
