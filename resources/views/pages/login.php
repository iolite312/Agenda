<div>
    <h1 class="text-2xl font-medium">Login</h1>
    <form action="/login" method="post" class="flex flex-col gap-2">
        <div>
            <label for="email">Email: </label>
            <input type="text" name="email" id="email" required class="border-slate-700 border-2 rounded">
        </div>
        <div>
            <label for="password">Password: </label>
            <input type="password" name="password" id="password" required class="border-slate-700 border-2 rounded">
        </div>
        <div>
            <button type="submit">Login</button>
        </div>
    </form>
</div>