KMail
=============================

Yii email extension (wrapper for swiftmailer)

## Installation

* Extract files into **protected/extensions/kmail**
* Add configuration:

```php
'components'=>array(
    ...
    'kmail' => array(
        'class'            => 'application.extensions.KMailExt',
        'viewPath'         => 'application.views.kmail',
        'transportType'    => "php", // or "smtp"
        /* uncomment and modify the following if using smtp
        'transportOptions' => array(
            'host'       => 'smtp.gmail.com',
            'username'   => 'xxx@gmail.com', // or email@googleappsdomain.com
            'password'   => 'yyy',
            'port'       => '465',
            'encryption' => 'ssl',
        ),
        */
    ),
    ...
),
```

## Usage

* Modify singleton file **KMailExt.php** (example functions included inside)
* Create and modify view files in **protected/views/kmail/** as needed
* Call function as needed

```php
// example call
Yii::app()->kmail->sendEmailActivationKey($user, $profile);
```

