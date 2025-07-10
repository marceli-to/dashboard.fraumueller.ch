# TODO

## Extend fingerprintjs/voter table

The fingerprintjs/voter table should contain more data about the user's browser and device:
- screen size
- timezone
- language
- ... and other data from fingerprintjs

We would still allow to vote multiple times but we would be able to "detect" if the same user is voting multiple times from the same device and mark the votes as potentially fraudulent. It's important to let the users allow to vote even if they are voting multiple times from the same device. But for the admin to be able to see that the votes are potentially fraudulent.


