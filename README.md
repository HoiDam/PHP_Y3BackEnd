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

Function 6: Add Wallet
- Request: Unknown
- Response: 

Function 7: Buy and Sell
- Request: Token/ Method (B or S) + Target Email + Amount(Coins/Funds)
- Response: Success/ Failed (with reason)

Function 8: Bitcoin wallet details


Function 9: Accept/Decline transaction request
- Request: Token + Status(Accept/Decline) + ID (of transaction)
- API Progress: Accept > BlockCypher to transaction
                Decline > Record Remain
- Response: Sucess/ Failed
            


##seed
1 :camera near patch donate sweet tree flip dust capable gap gallery cube
2 :frame illegal silver bind custom gun vast memory battle drill size prison
3 :smoke drink silent apple wine zero zoo sing expand impact ready asset


##seed_pw
bitcoin1
bitcoin2
bitcoin3

##wallet address
1 :mm4RzpyXnfnLg3aTHUCxPdYFjZS2bihB77
2 :muiiiiKf1scbcMy5X8WUJrp9G9YqbQo6Qi
3 :mrM8eqUyoumiEWTSyT4GVrJ8rBT8eJxUMq

##coins address
1 :n1Cfd9k5cd33R53T8quAXWYXWxe1QzepJi
2 :mmEcbSvNC4cFAHiV8xyxfPp9GtNeLYmKW8
3 :mjSyXgW2jzh8NZQxuXnHBWqvpNknvjgvmA

##blockcypher token
7a66a3ce406e4871b7694b4d24abca13



{
    "private": "2e447d8db07b4fcbb74064787b60fdd04dd7f30fc2be427457c795da246e3140",
    "public": "03fbbc6de19d0bf8e65008a8e25c8b1937d0978e5c4772793555e756fb9526eb98",
    "address": "mghMPCQ1ZnaC745b18wa91gNiQX4RyqRZC",
    "wif": "cP8e4BahVdTzKWwmUVkx5NMZZNTy1uxUiZQ1gYkSFV8gqrKfzcWe"
}

{
    "private": "ac0833632f370eed2aa2d89798c17d58d4734622bc54383e02eeac77ded8da79",
    "public": "038cc76458fdd122f29f2d067fe8b5fe91993a5428758a0f3c8a2e2c8cd044be31",
    "address": "mhv4shayymusncENLCyatPBevGgA1joZci",
    "wif": "cTM7JPeofAkfoznwZnyNXXz5XoU3PyC4fhdk72wMdGEsHVPteCcJ"
}
