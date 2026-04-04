<?php
namespace App\Page;

interface Messages {
    const
        WELCOME_MESSAGE = "Welcome To Elections Results Transfer System.
        Please Reply With Polling Agent Id and Password. e.g 'ID,Password'",

        RESULT_IS_VERIFIED = "Result Confirmed by Constituency Director. You cannot update it",

        SUCCESS_LOGIN = "",
        FAIL_LOGIN = "Invalid Account details. Please resend your correct Agent Id and Password. e.g \n  ID123,Password123",
        MENU = "
            Reply with 0 to 2. \n
            1 : Capture Result\n
            2 : Detail\n
            0 : Back\n
        "
	;

}
?>
