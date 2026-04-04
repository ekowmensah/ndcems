<?php
namespace App\Page;

interface Constant {
    const
        TWILIO_ACCOUNT_SID ="YOUR_TWILIO_ACCOUNT_SID",
        TWILIO_AUTH_TOKEN ="YOUR_TWILIO_AUTH_TOKEN",
        TWILIO_NUMBER ="YOUR_TWILIO_NUMBER",

		YES = "yes",
		NO = "no",
        STATUS_AVAILABLE = 1,


		STATUS_WELCOME = 0,
		STATUS_LOGIN = 1,
        STATUS_MENU = 2,
        STATUS_RESULT_UPDATE=3,
        STATUS_RESULT_UPDATE_SUBMIT=4,
        LOGOUT= 5,
        STATUS_DETAIL= 6,
        STATUS_DETAIL_ELECTION_RESULT= 7,

		POLLING_AGENT = 5


	;

}
?>
