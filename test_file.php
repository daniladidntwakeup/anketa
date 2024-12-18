<?php


$tel='+9272298078';
if (!preg_match('~^(?:\+7|8)\d{10}$~',$tel)) {
    $phoneErr = "Некорректно введён номер";
    $flagValidation = 1;
    echo 'не подошло';
}
else{
    echo 'подошло';
}

