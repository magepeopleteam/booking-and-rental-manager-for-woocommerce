/**
 * Booking and Rental Manager Blocks
 * 
 * This file contains all the block definitions for the Booking and Rental Manager plugin.
 */

(function(blocks, editor, components, i18n, element) {
    var el = element.createElement;
    var __ = i18n.__;
    var InspectorControls = editor.InspectorControls;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var RangeControl = components.RangeControl;
    var ToggleControl = components.ToggleControl;
    var ServerSideRender = components.ServerSideRender;

    // Register Rent List Block
    blocks.registerBlockType('rbfw/rent-list', {
        title: __('Rental Items List', 'booking-and-rental-manager-for-woocommerce'),
        icon: 'list-view',
        category: 'widgets',
        keywords: [
            __('rental', 'booking-and-rental-manager-for-woocommerce'),
            __('booking', 'booking-and-rental-manager-for-woocommerce'),
            __('list', 'booking-and-rental-manager-for-woocommerce')
        ],
        attributes: {
            style: {
                type: 'string',
                default: 'grid'
            },
            show: {
                type: 'number',
                default: -1
            },
            order: {
                type: 'string',
                default: 'DESC'
            },
            orderby: {
                type: 'string',
                default: ''
            },
            meta_key: {
                type: 'string',
                default: ''
            },
            type: {
                type: 'string',
                default: ''
            },
            location: {
                type: 'string',
                default: ''
            },
            category: {
                type: 'string',
                default: ''
            },
            cat_ids: {
                type: 'string',
                default: ''
            },
            columns: {
                type: 'number',
                default: 3
            },
            'left-filter': {
                type: 'string',
                default: ''
            },
            'left-title-filter': {
                type: 'string',
                default: 'on'
            },
            'left-price-filter': {
                type: 'string',
                default: 'on'
            },
            'left-location-filter': {
                type: 'string',
                default: 'on'
            },
            'left-category-filter': {
                type: 'string',
                default: 'on'
            },
            'left-type-filter': {
                type: 'string',
                default: 'on'
            },
            'left-feature-filter': {
                type: 'string',
                default: 'on'
            }
        },
        edit: function(props) {
            var attributes = props.attributes;

            return [
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, { title: __('Rental List Settings', 'booking-and-rental-manager-for-woocommerce'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Display Style', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.style,
                            options: [
                                { label: __('Grid', 'booking-and-rental-manager-for-woocommerce'), value: 'grid' },
                                { label: __('List', 'booking-and-rental-manager-for-woocommerce'), value: 'list' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ style: newValue });
                            }
                        }),
                        el(RangeControl, {
                            label: __('Number of Items to Show', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.show,
                            min: -1,
                            max: 100,
                            onChange: function(newValue) {
                                props.setAttributes({ show: newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Order', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.order,
                            options: [
                                { label: __('Descending', 'booking-and-rental-manager-for-woocommerce'), value: 'DESC' },
                                { label: __('Ascending', 'booking-and-rental-manager-for-woocommerce'), value: 'ASC' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ order: newValue });
                            }
                        }),
                        el(TextControl, {
                            label: __('Order By', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.orderby,
                            onChange: function(newValue) {
                                props.setAttributes({ orderby: newValue });
                            }
                        }),
                        el(TextControl, {
                            label: __('Meta Key', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.meta_key,
                            onChange: function(newValue) {
                                props.setAttributes({ meta_key: newValue });
                            }
                        }),
                        el(TextControl, {
                            label: __('Type', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.type,
                            onChange: function(newValue) {
                                props.setAttributes({ type: newValue });
                            }
                        }),
                        el(TextControl, {
                            label: __('Location', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.location,
                            onChange: function(newValue) {
                                props.setAttributes({ location: newValue });
                            }
                        }),
                        el(TextControl, {
                            label: __('Category', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.category,
                            onChange: function(newValue) {
                                props.setAttributes({ category: newValue });
                            }
                        }),
                        el(TextControl, {
                            label: __('Category IDs', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.cat_ids,
                            onChange: function(newValue) {
                                props.setAttributes({ cat_ids: newValue });
                            }
                        }),
                        el(RangeControl, {
                            label: __('Columns', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.columns,
                            min: 1,
                            max: 6,
                            onChange: function(newValue) {
                                props.setAttributes({ columns: newValue });
                            }
                        })
                    ),
                    el(PanelBody, { title: __('Left Filter Settings', 'booking-and-rental-manager-for-woocommerce'), initialOpen: false },
                        el(SelectControl, {
                            label: __('Show Left Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes['left-filter'],
                            options: [
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: '' },
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ 'left-filter': newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Show Title Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes['left-title-filter'],
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ 'left-title-filter': newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Show Price Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes['left-price-filter'],
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ 'left-price-filter': newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Show Location Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes['left-location-filter'],
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ 'left-location-filter': newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Show Category Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes['left-category-filter'],
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ 'left-category-filter': newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Show Type Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes['left-type-filter'],
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ 'left-type-filter': newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Show Feature Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes['left-feature-filter'],
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ 'left-feature-filter': newValue });
                            }
                        })
                    )
                ),
                el('div', { className: props.className },
                    el('div', { className: 'rbfw-block-preview' },
                        el('h3', {}, __('Rental Items List', 'booking-and-rental-manager-for-woocommerce')),
                        el('p', {}, __('This block displays a list of rental items.', 'booking-and-rental-manager-for-woocommerce')),
                        el('p', {}, __('Style: ', 'booking-and-rental-manager-for-woocommerce') + attributes.style),
                        el('p', {}, __('Items to show: ', 'booking-and-rental-manager-for-woocommerce') + attributes.show),
                        el('p', {}, __('Columns: ', 'booking-and-rental-manager-for-woocommerce') + attributes.columns)
                    )
                )
            ];
        },
        save: function() {
            return null; // Dynamic block, rendered on server
        }
    });

    // Register Rent Search Block
    blocks.registerBlockType('rbfw/rent-search', {
        title: __('Rental Search Form', 'booking-and-rental-manager-for-woocommerce'),
        icon: 'search',
        category: 'widgets',
        keywords: [
            __('rental', 'booking-and-rental-manager-for-woocommerce'),
            __('booking', 'booking-and-rental-manager-for-woocommerce'),
            __('search', 'booking-and-rental-manager-for-woocommerce')
        ],
        edit: function(props) {
            return el('div', { className: props.className },
                el('div', { className: 'rbfw-block-preview' },
                    el('h3', {}, __('Rental Search Form', 'booking-and-rental-manager-for-woocommerce')),
                    el('p', {}, __('This block displays a search form for rental items.', 'booking-and-rental-manager-for-woocommerce'))
                )
            );
        },
        save: function() {
            return null; // Dynamic block, rendered on server
        }
    });

    // Register Rent Filter Block
    blocks.registerBlockType('rbfw/rent-filter', {
        title: __('Rental Filter', 'booking-and-rental-manager-for-woocommerce'),
        icon: 'filter',
        category: 'widgets',
        keywords: [
            __('rental', 'booking-and-rental-manager-for-woocommerce'),
            __('booking', 'booking-and-rental-manager-for-woocommerce'),
            __('filter', 'booking-and-rental-manager-for-woocommerce')
        ],
        attributes: {
            title_filter_shown: {
                type: 'string',
                default: 'on'
            },
            price_filter_shown: {
                type: 'string',
                default: 'on'
            },
            location_filter_shown: {
                type: 'string',
                default: 'on'
            },
            category_filter_shown: {
                type: 'string',
                default: 'on'
            },
            type_filter_shown: {
                type: 'string',
                default: 'on'
            },
            feature_filter_shown: {
                type: 'string',
                default: 'on'
            }
        },
        edit: function(props) {
            var attributes = props.attributes;

            return [
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, { title: __('Filter Settings', 'booking-and-rental-manager-for-woocommerce'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Show Title Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.title_filter_shown,
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ title_filter_shown: newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Show Price Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.price_filter_shown,
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ price_filter_shown: newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Show Location Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.location_filter_shown,
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ location_filter_shown: newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Show Category Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.category_filter_shown,
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ category_filter_shown: newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Show Type Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.type_filter_shown,
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ type_filter_shown: newValue });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Show Feature Filter', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.feature_filter_shown,
                            options: [
                                { label: __('Yes', 'booking-and-rental-manager-for-woocommerce'), value: 'on' },
                                { label: __('No', 'booking-and-rental-manager-for-woocommerce'), value: 'off' }
                            ],
                            onChange: function(newValue) {
                                props.setAttributes({ feature_filter_shown: newValue });
                            }
                        })
                    )
                ),
                el('div', { className: props.className },
                    el('div', { className: 'rbfw-block-preview' },
                        el('h3', {}, __('Rental Filter', 'booking-and-rental-manager-for-woocommerce')),
                        el('p', {}, __('This block displays filters for rental items.', 'booking-and-rental-manager-for-woocommerce'))
                    )
                )
            ];
        },
        save: function() {
            return null; // Dynamic block, rendered on server
        }
    });

    // Register Add to Cart Block
    blocks.registerBlockType('rbfw/add-to-cart', {
        title: __('Rental Add to Cart', 'booking-and-rental-manager-for-woocommerce'),
        icon: 'cart',
        category: 'widgets',
        keywords: [
            __('rental', 'booking-and-rental-manager-for-woocommerce'),
            __('booking', 'booking-and-rental-manager-for-woocommerce'),
            __('cart', 'booking-and-rental-manager-for-woocommerce')
        ],
        attributes: {
            id: {
                type: 'number',
                default: 0
            }
        },
        edit: function(props) {
            var attributes = props.attributes;

            return [
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, { title: __('Add to Cart Settings', 'booking-and-rental-manager-for-woocommerce'), initialOpen: true },
                        el(TextControl, {
                            label: __('Rental Item ID', 'booking-and-rental-manager-for-woocommerce'),
                            value: attributes.id,
                            onChange: function(newValue) {
                                props.setAttributes({ id: parseInt(newValue) });
                            }
                        })
                    )
                ),
                el('div', { className: props.className },
                    el('div', { className: 'rbfw-block-preview' },
                        el('h3', {}, __('Rental Add to Cart', 'booking-and-rental-manager-for-woocommerce')),
                        el('p', {}, __('This block displays an add to cart button for a rental item.', 'booking-and-rental-manager-for-woocommerce')),
                        el('p', {}, __('Item ID: ', 'booking-and-rental-manager-for-woocommerce') + attributes.id)
                    )
                )
            ];
        },
        save: function() {
            return null; // Dynamic block, rendered on server
        }
    });

    // Register Search Results Block
    blocks.registerBlockType('rbfw/search-result', {
        title: __('Rental Search Results', 'booking-and-rental-manager-for-woocommerce'),
        icon: 'list-view',
        category: 'widgets',
        keywords: [
            __('rental', 'booking-and-rental-manager-for-woocommerce'),
            __('booking', 'booking-and-rental-manager-for-woocommerce'),
            __('search', 'booking-and-rental-manager-for-woocommerce')
        ],
        edit: function(props) {
            return el('div', { className: props.className },
                el('div', { className: 'rbfw-block-preview' },
                    el('h3', {}, __('Rental Search Results', 'booking-and-rental-manager-for-woocommerce')),
                    el('p', {}, __('This block displays search results for rental items.', 'booking-and-rental-manager-for-woocommerce'))
                )
            );
        },
        save: function() {
            return null; // Dynamic block, rendered on server
        }
    });

})(
    window.wp.blocks,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n,
    window.wp.element
);