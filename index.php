<?php
/*
Plugin Name: Apple Live Photo Embedder
Version: 1.0
Description: 通过短代码和自定义Gutenberg区块在文章中嵌入 Apple 实况照片
*/

// 引入 LivePhotosKit JS 库
function nglive_enqueue_livephotoskit_script() {
    wp_enqueue_script('nglive-livephotoskit-js', 'https://cdn.apple-livephotoskit.com/lpk/1/livephotoskit.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'nglive_enqueue_livephotoskit_script');

// 注册自定义 Gutenberg 区块
function nglive_register_custom_live_photos_block() {
    wp_register_script(
        'nglive-custom-live-photos-block',
        plugins_url('/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'block.js')
    );

    register_block_type('custom/live-photos-block', array(
        'editor_script' => 'nglive-custom-live-photos-block',
        'render_callback' => 'nglive_render_custom_live_photos_block'
    ));
}
add_action('init', 'nglive_register_custom_live_photos_block');

function nglive_render_custom_live_photos_block($attributes) {
    if (!isset($attributes['photoURL']) || !isset($attributes['videoURL'])) {
        return '';
    }

    $width = isset($attributes['width']) ? esc_attr($attributes['width']) . 'px' : '100%';
    $height = isset($attributes['height']) ? esc_attr($attributes['height']) . 'px' : '300px';

    return sprintf(
        '<div class="nglive-live-photo-wrapper" style="width:%s; height:%s; position:relative;">
            <div data-live-photo data-photo-src="%s" data-video-src="%s" style="width:100%%; height:100%%;"></div>
        </div>',
        $width,
        $height,
        esc_url($attributes['photoURL']),
        esc_url($attributes['videoURL'])
    );
}

// 添加基础样式
add_action('wp_head', function() {
    echo '<style>
        .nglive-live-photo-wrapper {
            margin: 1.5rem auto;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fa;
            position: relative;
        }
        .nglive-live-photo-wrapper::before {
            content: "轻点查看实况效果";
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.9em;
            color: rgba(255,255,255,0.8);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .nglive-live-photo-wrapper:hover::before {
            opacity: 1;
        }
    </style>';
});

// 确保在AJAX请求后加载 LivePhotosKit
add_action('wp_footer', function() {
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jQuery !== 'undefined') {
                jQuery(document).on('ajaxComplete', function(event, xhr, settings) {
                    if (document.querySelector('[data-live-photo]')) {
                        if (!window.LivePhotosKit) {
                            var script = document.createElement('script');
                            script.src = 'https://cdn.apple-livephotoskit.com/lpk/1/livephotoskit.js';
                            script.onload = function() {
                                initLivePhotos();
                            };
                            document.body.appendChild(script);
                        } else {
                            initLivePhotos();
                        }
                    }
                });
            }
        });

        function initLivePhotos() {
            var livePhotos = document.querySelectorAll('[data-live-photo]');
            livePhotos.forEach(function(livePhoto) {
                new LivePhotosKit.Player(livePhoto, {
                    photoSrc: livePhoto.getAttribute('data-photo-src'),
                    videoSrc: livePhoto.getAttribute('data-video-src')
                });
            });
        }
    </script>
    <?php
});



