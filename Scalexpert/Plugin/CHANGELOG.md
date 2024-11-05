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

1.3.0, 2024-06-07:
=============
    
    Added simulation widget on product page and payment page
    Bug fix: fix payment method display without fees on backoffice
    Fixed translations
    
1.3.1, 2024-07-22:
=============
    
    Added simulation widget on configurable product page
    Added csp whitelist
    Updated virtual product creation
    Bug fix: fix payment method display on checkout page
    Bug fix: inclusion of discount prices
    
1.4.0, 2024-07-25:
=============
    
    Added financing sub status in backoffice payment information
    Added new payment method "Long credit without fees FR"
    Added reorder button on financing failure page
    Bug fix: warranty personalization was not disabled when option not active on contract

1.5.0, 2024-08-28:
=============
    
    Added simulation widget on checkout cart
    Fixed translation

1.6.0, 2024-09-11:
=============
    
    Added libphonenumber library for validate and format phone number
    
1.6.1, 2024-09-30:
=============
    
    Minor code fixes
    
1.6.2, 2024-11-04:
=============
    
    Minor code fixes
    Bug fix: fix simulation widget datas when shipping rates are updated on cart or payment page
    
