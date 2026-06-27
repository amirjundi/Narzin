Welcome to {{ config('app.name') }}!

Hi {{ $user->name }},

Thanks for signing up! To get started, please verify your email address by visiting the link below:

{{ $url }}

This verification link will expire {{ $expiresIn }}.

If you did not create an account with us, you can safely ignore this email.

Best regards,
The {{ config('app.name') }} Team
