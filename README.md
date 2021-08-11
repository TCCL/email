# tccl/email

This project provides a PHP library for email generation. It supports generating HTML and plain text emails plus multipart emails with embedded attachments.

## Installation

~~~
$ composer require tccl/email
~~~

## Sample Usage

### Sending HTML email with style modifications

~~~php
<?php

use TCCL\Email\HTMLGenerator;
use TCCL\Email\Mailer;

$html = new HTMLGenerator;
$html->setContent(file_get_contents('test.html'));
$html->addModifier('*',[
    'styles' => 'max-width: 650px;font-family: sans-serif;',
    'exclude' => array_fill_keys(['html','center'],true),
]);
$html->addModifier('body',[
    'styles' => 'background-color: #f7f7f7',
]);

$mailer = new Mailer($html);
$mailer->sendMail(
    '😀 A test mail for testing purposes',
    Mailer::encodeEmail('Smith, John <john.smith@example.com>'),
    'jill.smith@example.com',
    'Smith, Jill'
);
~~~

### Sending an email with an attachment

~~~php
<?php

use TCCL\Email\Attachment;
use TCCL\Email\HTMLGenerator;
use TCCL\Email\Mailer;
use TCCL\Email\MultipartAlternative;
use TCCL\Email\MultipartMixed;
use TCCL\Email\PlainTextGenerator;

$html = new HTMLGenerator;
$html->setContent(file_get_contents('test.html'));
$html->addModifier('*',[
    'styles' => 'max-width: 650px;font-family: sans-serif;',
    'exclude' => array_fill_keys(['html','center'],true),
]);
$html->addModifier('body',[
    'styles' => 'background-color: #f7f7f7',
]);

$plain = new PlainTextGenerator;
$plain->setContent('Use the telephone, Luke.');

$attach = new Attachment('./files/map.pdf','TheMap.pdf');

$mixed = new MultipartMixed;
$alt = new MultipartAlternative;
$mixed->addPart($alt);
$alt->addPart($plain); // Alt should proceed html (for some reason...)
$alt->addPart($html);
$mixed->addPart($attach);

$mailer = new Mailer($mixed);
$mailer->send(
    'Test <sender@example.com>',
    'recipient@example.com',
    'A test mail for testing purposes'
);
~~~

### Embedding an inline image

~~~php
<?php

use TCCL\Email\HTMLGenerator;
use TCCL\Email\InlineImage;
use TCCL\Email\Mailer;
use TCCL\Email\MultipartRelated;

spl_autoload_register(function($class){
    $prefix = 'TCCL\Email\\';
    if (substr($class,0,strlen($prefix)) == $prefix) {
        $class = substr($class,strlen($prefix));
        $candidate = "../lib/$class.php";
        if (is_file($candidate)) {
            include $candidate;
        }
    }
});

$htmlRaw = <<<END
<p>This is an inline image test.</p>
<p>The following image is embedded in the email.</p>
<img src="my-image.png">
END;

// Create HTML email body.
$html = new HTMLGenerator;
$html->setContent($htmlRaw);

$inline = new InlineImage('./my-image.png');
InlineImage::linkTo($html);

$related = new MultipartRelated;
$related->addPart($html);
$related->addPart($inline);

$mailer = new Mailer($related);
$mailer->sendMail(
    '😀 A test mail for testing purposes',
    Mailer::encodeEmail('Smith, John <john.smith@example.com>'),
    'jill.smith@example.com',
    'Smith, Jill'
);
~~~
