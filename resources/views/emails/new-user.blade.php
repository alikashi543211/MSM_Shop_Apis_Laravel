<h2>Mailbox</h2>
<h4>Account Detail</h4>
<p><strong>email:</strong> {{ $user->email }}</p>
@if(!is_null($password))
    <p><strong>Password:</strong> {{ $password }}</p>
@endif
