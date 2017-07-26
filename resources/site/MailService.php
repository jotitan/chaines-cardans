<?php

class MailService{

       function send($to,$objet,$message){
         $headers = "From: TODO_your_mail\n";
         $headers .= "Reply-To: no-reply-ce@free.fr\n";
         $headers .= "Content-Type: text/html,text/plain; charset=\"utf8\"";
         mail($to,$objet,$message,$headers);
       }
  }

?>)