KMail
=============================

Yii email extension using swiftmailer
Follows singleton pattern to simplify email handling throughout the entire app

## Installation

* Extract files into **protected/extensions/kmail**
* Copy file **protected/extensions/kmail/KMail.php** to **protected/extensions/KMail.php**
* Add configuration:

```php
'components'=>array(
    ...
    'kmail' => array(
        'class'            => 'application.extensions.KMail',

        // optional configurations
        'viewPath'         => 'application.views.kmail', // path for view-based email templates
        'dryRun'           => false, // testing option
                                     // if 'dryRun' == true, then it will NOT send out real emails

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

* Put email code into file **KMail.php** (example functions included inside)
* Create and modify view files in `KMail->viewPath` as needed
* Call functions

```php
// example call using manual input
Yii::app()->kmail->sendTestEmail("some@email.com", "Test subject", "Test body");

// example call using view-based email template
Yii::app()->kmail->sendEmailActivationKey($user, $profile);
```

