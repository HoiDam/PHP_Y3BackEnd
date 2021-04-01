# PHP_Y3BackEnd
2 Parts

Boris:

Function 1: Login
- Login > Function: Username, PAssword (Request)
- Response: Token (saved in frontend also DB) @ Session

Function 2: Transaction History
- Request: Token (select id from )
- Response: Last 5 transaction records (max. 5 in sql)

Function 3: Register
- Request: Email + Username + Password
- Response: Success/ Failed

Function 4: Change Password
- Request: Current Password + New one
- Response: Success/ Failed

Function 5: Deposit and Withdraw
- Request: Method (Deposit/Withdraw) + Amount
- Response: Success/ Failed

Dam:

Function 6: Buy and Sell
- Request: Token/ Method (B or S) + Target Email + Amount(Coins/Funds)
- Response: Success/ Failed (with reason)

Function 7: Bitcoin wallet details
Function 8: Add Wallet
- Request: Unknown
- Response: 

Function 9: Accept/Decline transaction request
- Request: Token + Status(Accept/Decline) + ID (of transaction)
- API Progress: Accept > BlockCypher to transaction
                Decline > Record Remain
- Response: Sucess/ Failed
            
