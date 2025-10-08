<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import TaskAdd from '@/components/TaskAdd.vue'
import SubtaskAdd from '@/components/SubtaskAdd.vue'

function updateSubtaskCompleted(boardId: number, columnId: number, taskId: number, subtaskId: number, value: boolean)
{
    router.patch(
        `/boards/${boardId}/columns/${columnId}/tasks/${taskId}/subtasks/${subtaskId}`,
        {
            is_completed: value
        },
        {
            preserveScroll: true,
            preserveState: true,
        }
    )
}

type Subtask = {
  id: number
  title: string
  description?: string | null
  position: number
  due_date?: string | null
  is_completed: boolean
}

type Task = {
  id: number
  title: string
  description?: string | null
  position: number
  due_date?: string | null
  subtasks: Subtask[]
}

type Column = {
    id: number
    name: string
    position: number
    created_at: string
    tasks: Task[]
}

type Board = {
    id: number
    name: string
    position: number
    created_at: string
    columns: Column[]
}

const props = defineProps<{ 
    boards: Board[] 
    selectedBoard: Board | null
}>()

const form = useForm({ name: '' })
function submit(){
    form.post('/boards', {
        onSuccess: () => form.reset('name'),
        preserveScroll: true,
    });
}

function selectBoard(boardId:number)
{
    router.get('/boards',{selectedBoard: boardId}, {
        only: ['selectedBoard'],
        preserveScroll: true,
        preserveState: true,
    })
}

const columnForm = useForm({ name: '' })

function addColumn(){
    if (!props.selectedBoard) return
    columnForm.post(`/boards/${props.selectedBoard.id}/columns`, {
        only: ['selectedBoard'],
        onSuccess: () => columnForm.reset('name'),
        preserveScroll: true,
        preserveState: true
    })
}
</script>

<template>
    <Head title="Boards" />
    <div class="grid grid-cols-12 gap-6 p-6">
        <aside class="col-span-12 md:col-span-4 lg:col-span-3">
            <h1 class="text-2xl font-semibold">Boards page</h1>

            <form @submit.prevent="submit" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="board-name" class="block text-sm font-medium mb-1">
                        Board name
                    </label>
                    <input
                        id="board-name"
                        v-model="form.name"
                        type="text"
                        placeholder="e.g. Personal, Work"
                        class="rounded-xl border px-3 py-2 shadow-sm focus:outline-none focus:ring"
                    />
                    <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">
                        {{ form.errors.name }}
                    </p>
                </div>

                <button
                    type="submit"
                    class="rounded-xl border px-4 py-2 shadow-sm hover:bg-gray-50 disabled:opacity-50" :disabled="form.processing"
                >
                    Create Board
                </button>
            </form>

            <p v-if="props.boards.length === 0" class="text-gray-600">
                You don't have any boards yet.
            </p>

            <ul v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <li v-for="board in props.boards" :key="board.id" class="rounded-2xl border bg-white p-4 shadow-sm text-black" @click="selectBoard(board.id)">
                    <div class="font-medium">
                        {{ board.name }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Created {{ new Date(board.created_at).toLocaleDateString() }}
                    </div>
                </li>
            </ul>
        </aside>

        <main class="col-span-12 md:col-span-8 lg:col-span-9">
            <div v-if="!props.selectedBoard" class="text-gray-600">
                No board selected
            </div>

            <div v-else class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold">
                        {{ props.selectedBoard.name }}
                    </h2>
                </div>

                <!-- Add Column -->
                <form @submit.prevent="addColumn" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="columnName" class="block text-sm font-medium mb-1">
                            Column name
                        </label>
                        <input 
                            id="columnName"
                            v-model="columnForm.name"
                            type="text"
                            placeholder="e.g. To Do"
                            class="rounded-xl border px-3 py-2 shadow-sm focus:outline-none focus:ring"
                        />
                        <p v-if="columnForm.errors.name" class="text-sm text-red-600 mt-1">
                            {{ columnForm.errors.name }}
                        </p>
                    </div>

                    <button 
                        type="submit"
                        class="rounded-xl border px-4 py-2 shadow-sm hover:bg-gray-50 disabled:opacity-50"
                        :disabled="columnForm.processing"
                    >
                        Add Column
                    </button>
                </form>

                <div class="flex gap-4 overflow-x-auto">
                    <div v-for="column in props.selectedBoard.columns ?? []" :key="column.id" class="min-w-72 bg-gray-50 rounded-xl p-3">
                        <h3 class="font-medium mb-3 text-black">
                            {{ column.name }}
                        </h3>
                        <ul class="space-y-2">
                            <li v-for="task in column.tasks ?? []" :key="task.id" class="bg-white rounded-lg border p-3">
                                <p class="font-medium text-black">
                                    {{ task.title }}
                                </p>
                                <p v-if="task.description" class="text-sm text-gray-600 whitespace-pre-line">
                                    {{ task.description }}
                                </p>

                                <ul v-if="task.subtasks?.length" class="mt-2 space-y-1">
                                    <li v-for="subtask in task.subtasks" :key="subtask.id" class="flex items-start gap-2">
                                        <input type="checkbox" :checked="subtask.is_completed" class="mt-1" @change="updateSubtaskCompleted(props.selectedBoard.id, column.id, task.id, subtask.id, ($event.target as HTMLInputElement).checked)" />
                                        <p class="text-black">
                                            {{ subtask.title }}
                                        </p>
                                    </li>
                                </ul>
                                <!-- Add Subtask -->
                                <SubtaskAdd 
                                    :board-id="props.selectedBoard.id"
                                    :column-id="column.id"
                                    :task-id="task.id"
                                />

                                
                            </li>
                            <!-- Add Task -->
                            <TaskAdd 
                                :board-id="props.selectedBoard.id"
                                :column-id="column.id"
                            />
                        </ul>
                    </div>
                </div>
            </div>
        </main>
        

    </div>
</template>