KMail
=============================

Yii email extension using swiftmailer
Follows singleton pattern to simplify email handling throughout the entire app

## Installation

* Extract files into **protected/extensions/kmail**
* Copy file **protected/extensions/kmail/KMailExt.php** to **protected/extensions/KMailExt.php**
* Add configuration:

```php
'components'=>array(
    ...
    'kmail' => array(
        'class'            => 'application.extensions.KMailExt',

        // optional configurations
        'viewPath'         => 'application.views.kmail', // path for view-based email templates
        'dryRun'           => false,

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
* Create and modify view files in `KMail->viewPath` as needed
* Call function as needed

```php
// example call using manual input
Yii::app()->kmail->sendTestEmail("some@email.com", "Test subject", "Test body");

// example call using view-based email template
Yii::app()->kmail->sendEmailActivationKey($user, $profile);
```

