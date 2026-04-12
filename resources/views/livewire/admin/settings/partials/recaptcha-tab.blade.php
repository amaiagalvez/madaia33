{{-- reCAPTCHA site key --}}
<x-admin.form-input name="recaptchaSiteKey" :label="__('admin.settings_form.recaptcha_site_key')" />

{{-- reCAPTCHA secret key (password field) --}}
<x-admin.form-input name="recaptchaSecretKey" type="password" :label="__('admin.settings_form.recaptcha_secret_key')" />
