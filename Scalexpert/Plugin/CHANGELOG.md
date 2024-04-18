1.0.0, 2023-10-15:
=============

    Delivery of version 1.0.0 of plugin Magento CE & EE 2.4.3, 2.4.4 , 2.4.6
    content: e-financing solutions split payment & long term credit

1.1.0, 2023-11-10:
=============

    Added insurance
    Bug fix: fix succes page for guest orders
    
1.2.0, 2024-04-18:
=============

    Bug fix: error when refunding a split or long term credit payment
    Bug fix: financing and insurance information on order not updated by cron
    Bug fix: fix payment method custom position on checkout
    Bug fix: fix financing display for guest customer and missing icon on checkout payment page
    Bug fix: Fix financing eligibility for Germany country
    Removed insurance subscription ID on customer account order details
    Updated API fields for insurance and financing subscription
    Added phone number format verification on checkout
    Disabled payment option on back office configuration if merchant not subscribed when updated key
    Created invoice automatically for order if payment status is ACCEPTED
    Cancelled order when customer cancel the payment on payment page
    Possibility to excluded products by SKU on back office configuration for insurance and financing
    Added information message for customer on payment success page if the order contain a insurance and the payment is offline 
    Added information message for customer on payment failure page if API return an error 
    Minor code fixes
