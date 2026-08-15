<section class="auth-page">
    <form method="post" action="/admin/login" class="panel">
        <p class="eyebrow">ARCHON ADMINISTRATION</p>
        <h1>Secure sign in.</h1>
        <input type="hidden" name="_token" value="<?=Security::csrf()?>">
        <label>
            Admin email
            <input name="email" type="email" required autocomplete="username">
        </label>
        <label>
            Password
            <input name="password" type="password" required autocomplete="current-password">
        </label>
        <button class="button" type="submit">Sign in to administration</button>
    </form>
</section>
