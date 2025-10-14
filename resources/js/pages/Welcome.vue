<script setup lang="ts">

    import { Head } from '@inertiajs/vue3'
    import { reactive } from 'vue'
    import { computed } from 'vue';
    import { ref } from 'vue';
    import { nextTick } from 'vue';
    import { useForm } from '@inertiajs/vue3'
    import { watch } from 'vue';
    import { router } from '@inertiajs/vue3';

    // Boards
    type Board = {
        id:         number
        name:       string
        position:   number
        created_at: string
    }

    const props = defineProps<{
        boards: Board[]
        selectedBoard?: {
            id:         number
            name:       string
            columns:    {
                id:         number
                name:       string
                position:   number
            }[]
        } | null
    }>()

    const state = reactive({
        selectedBoardId: props.selectedBoard?.id ?? (props.boards[0]?.id ?? null),
    })

    /**watch(
        () => props.selectedBoardId,
        (newId) => {
            if (newId && newId !== state.selectedBoardId) {
                state.selectedBoardId = newId
            }
        }
    )**/

    function selectBoard(id:number) {
        state.selectedBoardId = id
        router.get('/', { selectedBoard: id }, 
            {
                only: ['selectedBoard'],
                preserveScroll:  true,
                preserveState: true,
            }
        )
    }

    /**const selectedBoard = computed(() => 
        props.boards.find(board => board.id === state.selectedBoardId) ?? null
    )**/
    const selectedBoard = ref(props.selectedBoard ?? null)
    watch(() => props.selectedBoard, (val) => { 
        selectedBoard.value = val 
        if (val?.id && val.id !== state.selectedBoardId)
        {
            state.selectedBoardId = val.id
        }
    })

    const showCreateBoard = ref(false)
    const firstFieldRef = ref<HTMLInputElement | null>(null)

    function openCreateBoard()
    {
        showCreateBoard.value = true
        nextTick(() => firstFieldRef.value?.focus())
    }

    function closeCreateBoard()
    {
        showCreateBoard.value = false
    }

    const createBoardForm = useForm({ 
        name: '',
        columns: ['ToDo', 'Doing'],
    })

    function submitCreateBoard()
    {
        createBoardForm.columns = createBoardForm.columns
            .map(column => (column ?? '').trim())
            .filter(column => column.length > 0)
        createBoardForm.post('/boards', {
            preserveScroll: true,
            onSuccess: () => {
                createBoardForm.reset('name', 'columns')
                createBoardForm.columns = ['ToDo', 'Doing']
                closeCreateBoard()
            }
        })
    }

    const hasSelectedBoard = computed(() => !!selectedBoard.value)
    const hasColumns = computed(() => ((selectedBoard.value?.columns?.length) || 0) > 0)

    // Columns
    const showAddColumn = ref(false)
    const firstColumnFieldRef = ref<HTMLInputElement | null>(null)

    const addColumnForm = reactive({
        name: '',
        colourMode: 'preset' as 'preset' | 'custom',
        preset: '#49C4E5',
        custom: '#635FC7'
    })

    // TEMP COLOURS
    const COLOUR_SWATCHES = [
        { name: 'Cyan', value: '#49C4E5'},
        { name: 'Purple', value: '#8471F2'},
        { name: 'Green', value: '#67E2AE'},
        { name: 'Indigo', value: '#635FC7'},
        { name: 'Orange', value: '#F59E0B'},
        { name: 'Rose', value: '#F43F5E'},
    ]

    function openAddColumn()
    {
        showAddColumn.value = true
        nextTick(() => firstColumnFieldRef.value?.focus())
    }

    function closeAddColumn()
    {
        showAddColumn.value = false
        addColumnForm.name = ''
        addColumnForm.colourMode = 'preset'
        addColumnForm.preset = COLOUR_SWATCHES[0].value
        addColumnForm.custom = '#635FC7'
    }

    // Validate Hex colour code like #RGB or #RRGGBB (case-insensitive)
    const HEX_REG = /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/
    const chosenColour = computed(() => addColumnForm.colourMode === 'preset' ? addColumnForm.preset : (HEX_REG.test(addColumnForm.custom) ? addColumnForm.custom : '#000000'))

    const canCreateColumn = computed(() => {
        const hasName = addColumnForm.name.trim().length > 0
        const colourOK = addColumnForm.colourMode === 'preset' ? true : HEX_REG.test(addColumnForm.custom.trim())
        return hasName && colourOK
    })

    function submitAddColumn()
    {
        if (!canCreateColumn.value) return

        closeAddColumn()
    }

</script>

<template>
    <Head title="Kanban" />

    <div class="min-h-screen grid grid-cols-12 gap-0 bg-white text-slate-900" :class="{ 'overflow-hidden': showCreateBoard }">

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

            <button type="button" class="mt-2 w-full flex items-center gap-3 rounded-r-full px-4 py-2 text-[#635FC7] hover:bg-[#F4F7FD] transition focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" @click="openCreateBoard()" aria-haspopup="dialog">
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

            <div class="pb-6">
                <div v-if="hasSelectedBoard && hasColumns" class="flex gap-6 overflow-x-auto" :key="selectedBoard?.id">
                    <div v-for="column in selectedBoard.columns" :key="column.id" class="min-w-[280px]">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="h-3 w-3 rounded-full"
                                    :style="{ backgroundColor: ['#49C4E5', '#8471F2', '#67E2AE'][(((column.position ?? 1) -1) % 3 + 3) % 3] }"></span>
                                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                                    {{ column.name }}
                                </h2>
                            <span class="text-xs text-slate-400">(0)</span>
                        </div>

                        <div class="rounded-xl bg-[#F4F7FD] p-4 text-center text-slate-500">
                            No tasks yet
                        </div>
                    </div>

                   <button type="button" class="min-w-[280px] rounded-xl bg-[#F4F7FD] text-[#635FC7] font-semibold text-sm grid place-items-center hover:opacity-80" @click="openAddColumn()">
                        + Add New Column
                    </button>

                </div>

                <div v-else-if="hasSelectedBoard" class="grid place-items-center h-[60vh]">
                    <div class="text-center max-w-md">
                        <h2 class="text-xl font-semibold">
                            This board is empty. Create a new column to get started.
                        </h2>
                        <button type="button" class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700" @click="openAddColumn()">
                            + Add New Column
                        </button>
                    </div>
                </div>

                <div v-else class="h-[70vh] grid place-items-center">
                    <div class="text-center max-w-md">
                        <h2 class="text-xl font-semibold">No board selected.</h2>
                    </div>
                </div>

            </div>

            <!-- Board Modal -->
            <div v-if="showCreateBoard" class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="createBoardTitle">
                <div class="absolute inset-0 bg-black/40">
                </div>

                <div class="absolute inset-0 grid place-items-center p-4" @click.self="closeCreateBoard">
                    <div class="w-full max-w-xl rounded-2xl bg-white shadow-xl ring-1 ring-black/5">
                
                        <div class="flex items-start justify-between p-6">
                            <h2 id="createBoardTitle" class="text-lg font-semibold text-slate-900">
                                Add New Board
                            </h2>
                            <button type="button" class="rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" @click="closeCreateBoard()" aria-label="Close">
                                <img src="/images/icon-cross.svg" alt="" class="h-3 w-3" />
                            </button>
                        </div>

                        <div class="px-6 pb-6 space-y-5">
                            <div>
                                <label for="boardNameInput" class="block text-sm font-medium text-slate-700 mb-1">
                                    Name
                                </label>
                                <input id="boardNameInput" ref="firstFieldRef" type="text" placeholder="e.g. Web Design" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-slate-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" v-model="createBoardForm.name"/>
                                <p v-if="createBoardForm.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ createBoardForm.errors.name }}
                                </p>
                            </div>

                            <div>
                                <p class="block text-sm font-medium text-slate-700 mb-2">
                                    Columns
                                </p>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2" v-for="(column, columnId) in createBoardForm.columns" :key="columnId">
                                        <input type="text" class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-slate-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" v-model="createBoardForm.columns[columnId]" placeholder="e.g. To Do" />
                                    
                                        <button type="button" class="shrink-0 rounded-md p-2" @click="createBoardForm.columns.splice(columnId, 1)" :disabled="createBoardForm.columns.length <= 1" :title="createBoardForm.columns.length <= 1 ? 'At least one column' : 'Remove'">
                                            <img src="/images/icon-cross.svg" alt="" class="h-3 w-3" />
                                        </button>
                                    </div>

                                </div>

                                <button type="button" class="mt-3 w-full rounded-2xl bg-[#F4F7FD] px-4 py-2 text-sm font-medium text-[#635FC7] hover:bg-[#E9ECFB] focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" @click="createBoardForm.columns.push('')">
                                    + Add New Column
                                </button>

                                <p v-if="createBoardForm.errors['columns']" class="mt-2 text-sm text-red-600">
                                    {{ createBoardForm.errors['columns'] }}
                                </p>

                                <p v-if="createBoardForm.errors['columns.*']" class="mt-2 text-sm text-red-600">
                                    {{ createBoardForm.errors['columns.*'] }}
                                </p>

                            </div>
                           
                        </div>

                        <div class="px-6 pb-6">
                            <button type="button" class="w-full rounded-2xl bg-[#635FC7] px-4 py-2 text-sm font-semibold text-white hover:bg-[#7B77FF] disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" :disabled="createBoardForm.processing" @click="submitCreateBoard">
                                {{ createBoardForm.processing ? 'Creating...' : 'Create New Board' }}
                            </button>
                        </div>
                        
                    </div>
                    
                </div>
            </div>

            <!-- Column Modal -->
            <div v-if="showAddColumn" class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="addColumnTitle">
                <div class="absolute inset-0 bg-black/40">
                </div>

                <div class="absolute inset-0 grid place-items-center p-4" @click.self="closeAddColumn">
                    <div class="w-full max-w-xl rounded-2xl bg-white shadow-xl ring-1 ring-black/5">
                        <div class="flex items-start justify-between p-6">
                            <h2 id="addColumnTitle" class="text-lg font-semibold text-slate-900">
                                Add New Column
                            </h2>
                             <button type="button" class="rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" @click="closeAddColumn()" aria-label="Close">
                                <img src="/images/icon-cross.svg" alt="" class="h-3 w-3" />
                            </button>
                        </div>

                        <div class="px-6 pb-6 space-y-5">
                            <div>
                                <label for="columnNameInput" class="block text-sm font-medium text-slate-700 mb-1">
                                    Column Name
                                </label>
                                <input id="columnNameInput" ref="firstColumnFieldRef" type="text" v-model="addColumnForm.name" placeholder="e.g. To Do" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-slate-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" />
                            </div>

                            <div class="space-y-2">
                                <p class="block text-sm font-medium text-slate-700">
                                    Colour
                                </p>
                                <div class="flex items-center gap-4 text-sm">
                                    <label class="inline-flex items-center gap-2">
                                        <input type="radio" value="preset" v-model="addColumnForm.colourMode" />
                                        Preset
                                    </label>
                                    <label class="inline-flex items-center gap-2">
                                        <input type="radio" value="custom" v-model="addColumnForm.colourMode" />
                                        Custom
                                    </label>
                                </div>
                                <div v-if="addColumnForm.colourMode === 'preset'" class="grid grid-cols-6 gap-2">
                                    <button v-for="colour in COLOUR_SWATCHES" :key="colour.value" type="button" class="group relative rounded-xl border p-2 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" :class="addColumnForm.preset === colour.value ? 'border-[#635FC7]' : 'border-slate-200'" @click="addColumnForm.preset = colour.value" :aria-pressed="addColumnForm.preset === colour.value">
                                        <span class="block h-5 w-5 rounded-full mx-auto" :style="{ backgroundColor: colour.value}">
                                        </span>
                                        <span class="mt-1 block text-[11px] text-slate-600 text-center truncate">
                                            {{ colour.name }}
                                        </span>
                                    </button>
                                </div>
                                <div v-else class="flex items-center gap-3">
                                    <input type="text" v-model="addColumnForm.custom" placeholder="#635FC7" class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-slate-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" />
                                    <span class="inline-block h-6 w-6 rounded-full border" :style="{ backgroundColor: HEX_REG.test(addColumnForm.custom) ? addColumnForm.custom : '#FFFFFF'}">
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 pt-2">
                                    <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: chosenColour }">
                                    </span>
                                    <span class="text-xs text-slate-500">
                                        Preview = {{ chosenColour }}
                                    </span>
                                </div>

                                <div class="px-6 pb-6">
                                    <button type="button" class="w-full rounded-2xl bg-[#635FC7] px-4 py-2 text-sm font-semibold text-white hover:bg-[#7B77FF] disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-[#A8A4FF]" :disabled="!canCreateColumn" @click="submitAddColumn">
                                        Create Column
                                    </button>
                                </div>

                            </div>
                        </div>
                    
                    </div>
                </div>

            </div>
        </main>

    </div>

    
</template>