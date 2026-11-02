<?php
/**
 * Plugin Name: Luxury Gold Login
 * Description: صفحه ورود اختصاصی وردپرس با طراحی مشکی، طلایی و سه‌بعدی
 * Version: 1.1.0
 * Author: Mohammadreza Kazemi
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * تنظیمات پیش‌فرض
 */
function lgl_get_settings() {
    $defaults = array(
        'logo'     => '',
        'title'    => 'خوش آمدید',
        'subtitle' => 'برای ورود به حساب کاربری خود اطلاعات را وارد کنید.',
    );

    $saved = get_option('lgl_settings', array());

    return wp_parse_args(is_array($saved) ? $saved : array(), $defaults);
}

/**
 * منوی تنظیمات
 */
add_action('admin_menu', function () {
    add_options_page(
        'طراحی صفحه ورود',
        'طراحی صفحه ورود',
        'manage_options',
        'luxury-gold-login',
        'lgl_render_settings_page'
    );
});

/**
 * ثبت و پاک‌سازی تنظیمات
 */
add_action('admin_init', function () {
    register_setting(
        'lgl_settings_group',
        'lgl_settings',
        array(
            'type'              => 'array',
            'sanitize_callback' => 'lgl_sanitize_settings',
            'default'           => array(),
        )
    );
});

function lgl_sanitize_settings($input) {
    $input = is_array($input) ? $input : array();

    return array(
        'logo' => isset($input['logo']) ? esc_url_raw(trim($input['logo'])) : '',
        'title' => isset($input['title'])
            ? sanitize_text_field($input['title'])
            : 'خوش آمدید',
        'subtitle' => isset($input['subtitle'])
            ? sanitize_text_field($input['subtitle'])
            : '',
    );
}

/**
 * صفحه تنظیمات افزونه
 */
function lgl_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = lgl_get_settings();
    ?>
    <div class="wrap" dir="rtl">
        <h1>طراحی اختصاصی صفحه ورود</h1>
        <p>لوگو و متن‌های صفحه ورود وردپرس را از این بخش مدیریت کنید.</p>

        <form method="post" action="options.php">
            <?php settings_fields('lgl_settings_group'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="lgl-logo-url">لوگو</label></th>
                    <td>
                        <input
                            type="url"
                            id="lgl-logo-url"
                            name="lgl_settings[logo]"
                            value="<?php echo esc_attr($settings['logo']); ?>"
                            class="regular-text"
                            placeholder="آدرس تصویر لوگو"
                        />
                        <button type="button" class="button" id="lgl-select-logo">انتخاب از کتابخانه رسانه</button>
                        <button type="button" class="button" id="lgl-clear-logo">حذف لوگو</button>
                        <p class="description">پیشنهاد: تصویر PNG شفاف با عرض حدود ۴۰۰ پیکسل.</p>
                        <img
                            id="lgl-logo-preview"
                            src="<?php echo esc_url($settings['logo']); ?>"
                            alt="پیش‌نمایش لوگو"
                            style="display:<?php echo $settings['logo'] ? 'block' : 'none'; ?>;max-width:220px;max-height:100px;object-fit:contain;margin-top:12px;background:#151515;padding:10px;border-radius:8px;"
                        />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lgl-title">عنوان</label></th>
                    <td>
                        <input
                            type="text"
                            id="lgl-title"
                            name="lgl_settings[title]"
                            value="<?php echo esc_attr($settings['title']); ?>"
                            class="regular-text"
                        />
                        <p class="description">این عنوان به‌عنوان متن جایگزین لوگو و عنوان دسترس‌پذیر استفاده می‌شود.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lgl-subtitle">توضیحات</label></th>
                    <td>
                        <input
                            type="text"
                            id="lgl-subtitle"
                            name="lgl_settings[subtitle]"
                            value="<?php echo esc_attr($settings['subtitle']); ?>"
                            class="regular-text"
                        />
                    </td>
                </tr>
            </table>

            <?php submit_button('ذخیره تنظیمات'); ?>
        </form>

        <p>
            <a class="button button-primary" href="<?php echo esc_url(wp_login_url()); ?>" target="_blank" rel="noopener">
                مشاهده صفحه ورود
            </a>
        </p>
    </div>
    <?php
}

/**
 * انتخاب لوگو از کتابخانه رسانه
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'settings_page_luxury-gold-login') {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('jquery');

    $js = <<<'JS'
jQuery(function($) {
    let lglFrame;

    $('#lgl-select-logo').on('click', function(e) {
        e.preventDefault();

        if (lglFrame) {
            lglFrame.open();
            return;
        }

        lglFrame = wp.media({
            title: 'انتخاب لوگو',
            button: { text: 'استفاده از این لوگو' },
            multiple: false,
            library: { type: 'image' }
        });

        lglFrame.on('select', function() {
            const item = lglFrame.state().get('selection').first().toJSON();
            $('#lgl-logo-url').val(item.url);
            $('#lgl-logo-preview').attr('src', item.url).css('display', 'block');
        });

        lglFrame.open();
    });

    $('#lgl-clear-logo').on('click', function(e) {
        e.preventDefault();
        $('#lgl-logo-url').val('');
        $('#lgl-logo-preview').attr('src', '').hide();
    });
});
JS;

    wp_add_inline_script('jquery', $js);
});

/**
 * لینک و عنوان لوگوی بالای فرم
 */
add_filter('login_headerurl', function () {
    return home_url('/');
});

add_filter('login_headertext', function () {
    $settings = lgl_get_settings();
    return $settings['title'] !== '' ? $settings['title'] : get_bloginfo('name');
});

/**
 * توضیحات بالای فرم ورود
 */
add_filter('login_message', function ($message) {
    $settings = lgl_get_settings();

    if (empty($settings['subtitle'])) {
        return $message;
    }

    return $message . '<div class="lgl-subtitle">' . esc_html($settings['subtitle']) . '</div>';
});

/**
 * استایل صفحه ورود
 */
add_action('login_enqueue_scripts', function () {
    $settings = lgl_get_settings();
    $logo = $settings['logo'];

    $logo_css = '';
    if (!empty($logo)) {
        $logo_css = "
            body.login #login h1 a {
                background-image: url('" . esc_url($logo) . "') !important;
                background-size: contain !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
                width: 100% !important;
                max-width: 240px !important;
                height: 90px !important;
                margin: 0 auto 14px !important;
                font-size: 0 !important;
                text-indent: -9999px !important;
                overflow: hidden !important;
            }
        ";
    }

    $css = <<<'CSS'
body.login {
    --lgl-gold: #e9bc62;
    --lgl-gold-light: #fff0bd;
    --lgl-muted: #a4a4ac;

    min-height: 100vh;
    box-sizing: border-box;
    margin: 0;
    padding: 32px 16px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow-x: hidden;
    color: #f8f8f8;
    font-family: Tahoma, Arial, sans-serif;

    background:
        radial-gradient(ellipse at 50% 42%, rgba(132,91,25,.16), transparent 45%),
        radial-gradient(ellipse at 90% 5%, rgba(214,163,67,.08), transparent 32%),
        linear-gradient(145deg, #111216 0%, #07080a 55%, #11100d 100%);
}

body.login::before {
    content: "";
    position: fixed;
    inset: -35%;
    pointer-events: none;
    z-index: 0;
    background:
        linear-gradient(118deg, transparent 42%, rgba(233,188,98,.04) 42.1%, rgba(233,188,98,.32) 42.18%, rgba(255,224,155,.65) 42.22%, rgba(233,188,98,.08) 42.28%, transparent 42.5%),
        linear-gradient(118deg, transparent 62%, rgba(233,188,98,.05) 62.1%, rgba(233,188,98,.26) 62.2%, transparent 62.35%);
    transform: rotate(-5deg);
}

body.login::after {
    content: "";
    position: fixed;
    width: 340px;
    height: 340px;
    right: -180px;
    bottom: -160px;
    border: 1px solid rgba(233,188,98,.28);
    border-radius: 50%;
    box-shadow: 0 0 35px rgba(233,188,98,.06), inset 0 0 35px rgba(233,188,98,.04);
    pointer-events: none;
    z-index: 0;
}

body.login #login {
    width: 100%;
    max-width: 410px;
    box-sizing: border-box;
    padding: 34px 32px 28px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
    border: 1px solid rgba(233,188,98,.32);
    border-radius: 22px;
    background: linear-gradient(145deg, rgba(34,34,37,.97), rgba(13,14,17,.98) 55%, rgba(20,18,15,.98));
    box-shadow:
        0 28px 75px rgba(0,0,0,.55),
        0 0 0 1px rgba(255,255,255,.025),
        0 0 35px rgba(233,188,98,.075),
        inset 0 1px 0 rgba(255,255,255,.07);
    transform: translateZ(0);
}

body.login #login::before {
    content: "";
    position: absolute;
    top: -1px;
    left: 22%;
    width: 56%;
    height: 2px;
    border-radius: 10px;
    background: linear-gradient(90deg, transparent, var(--lgl-gold-light), var(--lgl-gold), transparent);
    box-shadow: 0 0 14px rgba(233,188,98,.6);
}

body.login #login h1 {
    margin: 0 0 18px;
}

body.login #login h1 a {
    background-image: none;
    width: 100%;
    height: auto;
    min-height: 40px;
    margin: 0 auto;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    color: var(--lgl-gold-light);
    font-family: Tahoma, Arial, sans-serif;
    font-size: 22px;
    font-weight: 700;
    line-height: 1.6;
    text-decoration: none;
    text-indent: 0;
    text-shadow: 0 0 18px rgba(233,188,98,.15);
}

body.login #login h1::after {
    content: "SECURE ACCESS";
    display: block;
    margin-top: 5px;
    text-align: center;
    color: var(--lgl-gold);
    font-size: 9px;
    letter-spacing: 4px;
    opacity: .75;
}

body.login #login .lgl-subtitle {
    margin: 0 0 18px;
    color: #b8b8bf;
    font-size: 12px;
    line-height: 2;
    text-align: center;
}

body.login #login form {
    margin-top: 18px;
    padding: 0;
    overflow: hidden;
    background: transparent;
    border: 0;
    box-shadow: none;
}

body.login #login form label {
    display: block;
    margin-bottom: 8px;
    color: #e8e8ec;
    font-size: 13px;
    font-weight: 500;
}

body.login #login form .input,
body.login #login form input[type="text"],
body.login #login form input[type="password"],
body.login #login form input[type="email"] {
    width: 100%;
    min-height: 48px;
    margin: 0 0 19px;
    padding: 11px 14px;
    box-sizing: border-box;
    border: 1px solid #36363b;
    border-radius: 10px;
    background: #101114;
    color: #fff;
    font-family: Tahoma, Arial, sans-serif;
    font-size: 14px;
    box-shadow: inset 0 2px 7px rgba(0,0,0,.25);
    outline: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

body.login #login form .input:focus {
    border-color: var(--lgl-gold);
    box-shadow: 0 0 0 3px rgba(233,188,98,.08), 0 0 16px rgba(233,188,98,.08);
}

body.login #login .wp-pwd {
    position: relative;
}

body.login #login .wp-pwd .input {
    padding-left: 42px;
}

body.login #login .wp-pwd button {
    color: var(--lgl-gold);
}

body.login #login .wp-pwd button:hover {
    color: var(--lgl-gold-light);
}

body.login #login .forgetmenot {
    float: none;
    display: block;
    margin: 0 0 18px;
}

body.login #login .forgetmenot label {
    color: var(--lgl-muted);
    font-size: 12px;
}

body.login #login input[type="checkbox"] {
    accent-color: var(--lgl-gold);
}

body.login #login .button-primary {
    float: none;
    display: block;
    width: 100%;
    min-height: 49px;
    padding: 12px 16px;
    margin: 4px 0 0;
    border: 1px solid #f1d38c;
    border-radius: 10px;
    background: linear-gradient(115deg, #b8872e 0%, #f5d88e 45%, #c99a42 100%);
    color: #17120a;
    font-family: Tahoma, Arial, sans-serif;
    font-size: 14px;
    font-weight: 800;
    text-shadow: none;
    box-shadow: 0 7px 20px rgba(199,151,57,.15), inset 0 1px 0 rgba(255,255,255,.45);
    cursor: pointer;
    transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
}

body.login #login .button-primary:hover {
    background: linear-gradient(115deg, #d2a54d, #ffedb7, #e0b65c);
    color: #100e09;
    box-shadow: 0 9px 28px rgba(233,188,98,.24), 0 0 15px rgba(233,188,98,.13);
    transform: translateY(-1px);
}

body.login #login .button-primary:active {
    transform: translateY(1px);
}

body.login #login .message,
body.login #login .notice,
body.login #login #login_error {
    margin: 0 0 18px;
    padding: 12px 14px;
    border-radius: 9px;
    border: 1px solid rgba(233,188,98,.22);
    background: #191817;
    color: #f1dca9;
    font-size: 12px;
    line-height: 1.9;
    box-shadow: none;
}

body.login #login #login_error {
    border-right: 3px solid #e58b73;
    color: #ffc0ad;
}

body.login #nav,
body.login #backtoblog {
    padding: 0;
    margin: 17px 0 0;
    text-align: center;
    font-size: 12px;
}

body.login #nav a,
body.login #backtoblog a {
    color: #c9c9ce !important;
    text-decoration: none;
    transition: color .2s ease;
}

body.login #nav a:hover,
body.login #backtoblog a:hover {
    color: var(--lgl-gold-light) !important;
}

body.login .language-switcher {
    position: relative;
    z-index: 1;
    margin-top: 20px;
}

body.login .language-switcher label {
    color: #aaa;
}

body.login .language-switcher select {
    background: #111216;
    color: #eee;
    border-color: #38332a;
    border-radius: 6px;
}

@media screen and (max-width: 480px) {
    body.login {
        padding: 22px 14px;
    }

    body.login #login {
        padding: 28px 22px 24px;
        border-radius: 18px;
    }

    body.login #login h1 a {
        font-size: 20px;
    }
}

@media (prefers-reduced-motion: reduce) {
    body.login #login *,
    body.login #login *::before,
    body.login #login *::after {
        transition: none !important;
    }
}
CSS;

    wp_register_style('lgl-login-style', false, array(), '1.1.0');
    wp_enqueue_style('lgl-login-style');
    wp_add_inline_style('lgl-login-style', $css . $logo_css);
}, 20);
