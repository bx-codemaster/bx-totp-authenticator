<?php
  /* --------------------------------------------------------------
   $Id: bx_two_factor_authenticator.js.php 15291 2026-02-01 12:00:00Z benax $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
   if ( defined('MODULE_BX_TOTP_AUTHENTICATOR_STATUS') && 'True' == MODULE_BX_TOTP_AUTHENTICATOR_STATUS) {
?>
<script>
  (function() {
    const inputs_totp = document.querySelectorAll('#totp_code_1, #totp_code_2, #totp_code_3, #totp_code_4, #totp_code_5, #totp_code_6');
    const hiddenInput = document.getElementById('totp_code');
    
    inputs_totp.forEach((input, index) => {
      input.addEventListener('input', function(e) {
        if (this.value.length === 1 && index < inputs_totp.length - 1) {
          inputs_totp[index + 1].focus();
        }
        updateHiddenField_totp();
      });
      
      input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '' && index > 0) {
          inputs_totp[index - 1].focus();
        }
      });
      
      input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasteData = e.clipboardData.getData('text').replace(/\D/g, '').substring(0, 6);
        pasteData.split('').forEach((char, i) => {
          if (inputs_totp[i]) inputs_totp[i].value = char;
        });
        if (pasteData.length > 0) inputs_totp[Math.min(pasteData.length, 5)].focus();
        updateHiddenField_totp();
      });
    });
    
    function updateHiddenField_totp() {
      hiddenInput.value = Array.from(inputs_totp).map(i => i.value).join('');
    }

    const inputs_email = document.querySelectorAll('#email_code_1, #email_code_2, #email_code_3, #email_code_4, #email_code_5, #email_code_6');
    const hiddenInput_email = document.getElementById('email_code');
    
    inputs_email.forEach((input, index) => {
      input.addEventListener('input', function(e) {
        if (this.value.length === 1 && index < inputs_email.length - 1) {
          inputs_email[index + 1].focus();
        }
        updateHiddenField_email();
      });
      
      input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '' && index > 0) {
          inputs_email[index - 1].focus();
        }
      });
      
      input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasteData = e.clipboardData.getData('text').replace(/\D/g, '').substring(0, 6);
        pasteData.split('').forEach((char, i) => {
          if (inputs_email[i]) inputs_email[i].value = char;
        });
        if (pasteData.length > 0) inputs_email[Math.min(pasteData.length, 5)].focus();
        updateHiddenField_email();
      });
    });
    
    function updateHiddenField_email() {
      hiddenInput_email.value = Array.from(inputs_email).map(i => i.value).join('');
    }
<?php if (basename($_SERVER['PHP_SELF']) == 'bx_two_factor_verify.php') { ?>
    var timerData = document.getElementById('timer-data');
    var emailExpires = parseInt(timerData.dataset.emailExpires) || 0;
    var sessionExpires = parseInt(timerData.dataset.sessionExpires);
    var cancelUrl = timerData.dataset.cancelUrl;

    if (emailExpires > 0) {
      var emailTimer = setInterval(function() {
        emailExpires--;
        if (emailExpires <= 0) {
          clearInterval(emailTimer);
          var emailTimerEl = document.getElementById('email-timer');
          if (emailTimerEl) emailTimerEl.textContent = '0';
        } else {
          var emailTimerEl = document.getElementById('email-timer');
          if (emailTimerEl) emailTimerEl.textContent = emailExpires;
        }
      }, 1000);
    }

    var sessionTimer = setInterval(function() {
      sessionExpires--;
      if (sessionExpires <= 0) {
        clearInterval(sessionTimer);
        window.location.href = cancelUrl;
      } else {
        var sessionTimerEl = document.getElementById('session-timer');
        if (sessionTimerEl) sessionTimerEl.textContent = sessionExpires;
      }
    }, 1000);
<?php } ?>
  })();
</script> 
<?php
  } 
?>