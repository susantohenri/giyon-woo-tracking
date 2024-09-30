giyon_woo_tracking.metabox = jQuery(giyon_woo_tracking.form_selector)

jQuery(() => {
    const current_url = window.location.href
    const split = current_url.split(`id=`)
    const order_id = split[1]

    giyon_woo_tracking_get(order_id)

    giyon_woo_tracking.metabox
        .find(`[type="text"]`).keypress(e => {
            if (13 === e.keyCode) {
                e.preventDefault()
                giyon_woo_tracking.metabox.find(`button`).click()
            }
        })

    giyon_woo_tracking.metabox
        .find(`button`)
        .click(e => {
            e.preventDefault()
            giyon_woo_tracking_set(order_id)
        })
})

function giyon_woo_tracking_get(order_id) {
    jQuery.get(giyon_woo_tracking.ajax, {
        action: `giyon_woo_tracking_get`,
        order_id
    }, response => {
        response = JSON.parse(response)
        giyon_woo_tracking.metabox.find(`select`).val(response.shipping_company)
        giyon_woo_tracking.metabox.find(`input`).eq(0).val(response.tracking_number)
        giyon_woo_tracking.metabox.find(`input`).eq(1).val(response.tracking_link)
    })
}

function giyon_woo_tracking_set(order_id) {
    jQuery.post(giyon_woo_tracking.ajax, {
        action: `giyon_woo_tracking_set`,
        order_id,
        shipping_company: giyon_woo_tracking.metabox.find(`select`).val(),
        tracking_number: giyon_woo_tracking.metabox.find(`input`).eq(0).val()
    }, response => {
        giyon_woo_tracking_get(order_id)
        jQuery(`.giyon-woo-tracking div:has(b)`).show()
        setTimeout(() => {
            jQuery(`.giyon-woo-tracking div:has(b)`).fadeOut()
        }, 2000)
    })
}