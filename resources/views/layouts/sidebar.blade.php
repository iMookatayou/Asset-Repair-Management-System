<aside class="w-64 bg-white dark:bg-zinc-950 border-r border-zinc-200 dark:border-zinc-800 flex flex-col">
  <div class="h-14 flex items-center justify-center border-b border-zinc-200 dark:border-zinc-800">
    <span class="text-xl font-bold text-emerald-600">PPK-Asset</span>
  </div>

  <nav class="flex-1 overflow-y-auto py-4 space-y-1">
    <a href="{{ route('repair.dashboard') }}"
       class="block px-6 py-2 text-sm font-medium {{ request()->routeIs('repair.dashboard') ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
       📊 Dashboard
    </a>

    <a href="{{ route('maintenance.requests.index') }}"
       class="block px-6 py-2 text-sm font-medium {{ request()->routeIs('maintenance.requests.*') ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
       🧰 Maintenance
    </a>

    <a href="{{ route('profile.edit') }}"
       class="block px-6 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
       👤 Profile
    </a>
  </nav>
</aside>
