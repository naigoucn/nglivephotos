(function (blocks, editor, element, components) {
    var el = element.createElement;
    var MediaUpload = editor.MediaUpload;
    var InspectorControls = editor.InspectorControls;
    var TextControl = components.TextControl;

    blocks.registerBlockType('custom/live-photos-block', {
        title: 'Live Photos Block',
        icon: 'camera',
        category: 'media',
        attributes: {
            photoURL: {
                type: 'string',
                default: ''
            },
            videoURL: {
                type: 'string',
                default: ''
            },
            width: {
                type: 'number',
                default: 400
            },
            height: {
                type: 'number',
                default: 300
            }
        },

        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            const onPhotoSelect = (media) => {
                setAttributes({ photoURL: media.url });
                // 自动设置宽度和高度
                if (media.sizes && media.sizes.full) {
                    setAttributes({
                        width: media.sizes.full.width,
                        height: media.sizes.full.height
                    });
                }
            };

            const onVideoSelect = (media) => {
                setAttributes({ videoURL: media.url });
            };

            return el(
                'div',
                { className: props.className },
                el('p', {}, '选择图片和视频：'),
                el(
                    MediaUpload,
                    {
                        onSelect: onPhotoSelect,
                        allowedTypes: ['image'],
                        render: function (obj) {
                            return el(components.Button, {
                                className: 'button button-large',
                                onClick: obj.open
                            }, '选择图片');
                        }
                    }
                ),
                el(TextControl, {
                    label: '图片 URL',
                    value: attributes.photoURL,
                    onChange: function (value) {
                        setAttributes({ photoURL: value });
                    }
                }),
                el(
                    MediaUpload,
                    {
                        onSelect: onVideoSelect,
                        allowedTypes: ['video'],
                        render: function (obj) {
                            return el(components.Button, {
                                className: 'button button-large',
                                onClick: obj.open
                            }, '选择视频');
                        }
                    }
                ),
                el(TextControl, {
                    label: '视频 URL',
                    value: attributes.videoURL,
                    onChange: function (value) {
                        setAttributes({ videoURL: value });
                    }
                }),
                el(InspectorControls, {},
                    el(TextControl, {
                        label: '宽度(px)',
                        value: attributes.width,
                        onChange: function (value) {
                            setAttributes({ width: parseInt(value, 10) || 0 });
                        }
                    }),
                    el(TextControl, {
                        label: '高度(px)',
                        value: attributes.height,
                        onChange: function (value) {
                            setAttributes({ height: parseInt(value, 10) || 0 });
                        }
                    })
                )
            );
        },

        save: function () {
            // 后台通过 PHP 渲染，前端保存为空
            return null;
        }
    });
}(
    window.wp.blocks,
    window.wp.editor,
    window.wp.element,
    window.wp.components
));