<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import { reactive } from 'vue'
    import { computed } from 'vue';

    // Boards
    type Board = {
        id:         number
        name:       string
        position:   number
        created_at: string
    }

    const props = defineProps<{
        boards: Board[]
    }>()

    const state = reactive({
        selectedBoardId: props.boards.length > 0 ? props.boards[0].id : null,
    })

    function selectBoard(id:number) {
        state.selectedBoardId = id
    }

    const selectedBoard = computed(() => 
        props.boards.find(board => board.id === state.selectedBoardId) ?? null
    )

</script>

<template>
    <Head title="Kanban" />

    <div class="min-h-screen grid grid-cols-12 gap-0 bg-white text-slate-900">

        <aside class="col-span-12 md:col-span-4 lg:col-span-3 border-r border-slate-200 p-6 bg-white flex flex-col">
            <div class="mb-8 flex items-center gap-3 px-4">
                <img src="/images/logo-dark.svg" alt="Kanban" class="h-6" />
            </div>

            <p class="mb-3 px-4 text-[11px] font-semibold tracking-[0.2em] text-slate-500">
                ALL BOARDS ({{ props.boards.length }})
            </p>

            <nav class="space-y-2" role="navigation" aria-label="Boards">
                <button v-for="board in props.boards" :key="board.id" @click="selectBoard(board.id)" class="group w-full flex items-center gap-3 rounded-r-full px-4 py-2 text-left transition focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" :class="state.selectedBoardId === board.id ? 'bg-[#635FC7] text-white hover:bg-[#A8A4FF]' : 'text-slate-600 hover:bg-[#F4F7FD]'" :aria-current="state.selectedBoardId === board.id ? 'page' : undefined">
                    <img src="/images/icon-board.svg" alt="" class="h-5 w-5" />
                    <span class="truncate">
                        {{ board.name }}
                    </span>
                </button>
            </nav>

            <button type="button" class="mt-2 w-full flex items-center gap-3 rounded-r-full px-4 py-2 text-[#635FC7] hover:bg-[#F4F7FD] transition focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]">
                <img src="/images/icon-board.svg" alt="" class="h-5 w-5" />
                <span class="truncate">
                    + Create New Board
                </span>
            </button>

            <!-- Spacer -->
            <div class="flex-1"></div>

            <div class="rounded-xl bg-[#F4F7FD] p-3">
                <div class="flex items-center justify-between gap-3">
                    <span>
                        <img src="/images/icon-light-theme.svg" alt="" class="h-5 w-5" />
                    </span>

                    <button type="button" class="relative h-6 w-12 rounded-full bg-[#635FC7] outline-none ring-offset-2 focus:ring-2  focus:ring-[#A8A4FF]" aria-label="Toggle theme">
                        <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform will-change-transform" style="transform: translateX(0px)">
                        </span>
                    </button>

                    <span>
                        <img src="/images/icon-dark-theme.svg" alt="" class="h-5 w-5" />
                    </span>
                </div>
            </div>

            <button type="button" class="mt-3 flex items-center gap-2 px-2 py-1 text-slate-500 hover:text-slate-700 rounded-md focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]">
                <img src="/images/icon-hide-sidebar.svg" alt="" class="h-5 w-5" />
                <span class="text-sm">
                    Hide Sidebar
                </span>
            </button>

        </aside>

        <main class="col-span-12 md:col-span-8 lg:col-span-9 p-6">

            <div class="mb-6 flex items-center justify-between border-b border-slate-200 pb-4">
                <h1 class="text-lg md:text-xl font-semibold tracking-tight">
                    {{ selectedBoard ? selectedBoard.name : "Board" }}
                </h1>

                <div class="flex items-center gap-2">
                    <button type="button" class="hidden sm:inline-flex items-center rounded-full bg-[#635FC7] px-4 py-2 text-sm font-medium text-white hover:bg-[#A8A4FF] disabled:opacity-50 disabled:cursor-not-allowed" :disabled="true">
                        + Add New Task
                    </button>

                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-md text-slate-500 hover:bg-[#F4F7FD] focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" aria-label="Board options">
                        <img src="/images/icon-vertical-ellipsis.svg" alt="" class="h-5 w-1" />
                    </button>
                </div>
            </div>

            <div class="h-[70vh] grid place-items-center">
                <div class="text-center max-w-md">
                    <h2 class="text-xl font-semibold">
                        This board is empty. Create a new column to get started.
                    </h2>
                    <button type="button" class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        + Add New Column
                    </button>
                </div>
            </div>
        </main>

    </div>
</template>