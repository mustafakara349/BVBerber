<x-mail::message>
# Şifre Sıfırlama Talebi

Merhaba,

BVBerber uygulamasındaki hesabınızın şifresini sıfırlamak için bir talepte bulundunuz. 
Şifrenizi yenilemek için aşağıdaki 6 haneli kodu kullanabilirsiniz.

<x-mail::panel>
# {{ $otp }}
</x-mail::panel>

Bu kod 15 dakika boyunca geçerlidir. Eğer şifre sıfırlama talebinde bulunmadıysanız bu e-postayı dikkate almayınız.

Teşekkürler,<br>
{{ config('app.name') }}
</x-mail::message>
