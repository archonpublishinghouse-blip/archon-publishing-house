<section class="auth-page">
    <form method="post" action="/admin/login" class="panel">
        <p class="eyebrow">ARCHON CRM</p>
        <h1>Secure team sign in.</h1>
        <input type="hidden" name="_token" value="<?=Security::csrf()?>">
        <label>
            CRM email
            <input name="email" type="email" required autocomplete="username">
        </label>
        <label>
            Password
            <input name="password" type="password" required autocomplete="current-password">
        </label>
        <button class="button" type="submit">Sign in to CRM</button>
        <p class="small-note">Admin and team member access is decided by the credentials used here.</p>
    </form>
</section>
