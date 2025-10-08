<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import TaskAdd from '@/components/TaskAdd.vue'
import SubtaskAdd from '@/components/SubtaskAdd.vue'
import { router } from '@inertiajs/vue3'

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
    columns: Column[]
}

const props = defineProps<{ board: Board }>()

const columnForm = useForm({ name: '' })

function addColumn(){
    columnForm.post(`/boards/${props.board.id}/columns`, {
        onSuccess: () => columnForm.reset('name'),
        preserveScroll: true,
    })
}

</script>

<template>
    <Head :title="props.board.name" />

    <div class="p-6 space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">
                {{ props.board.name }}
            </h1>
            <Link href="/boards" class="text-sm underline">Back to boards</Link>
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

        <!-- Column list -->
        <p v-if="props.board.columns.length === 0" class="text-gray-600">
            This board has no columns yet
        </p>

        <ul v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <li
                v-for="column in props.board.columns"
                :key="column.id"
                class="rounded-2xl border bg-white p-4 shadow-sm text-black"
            >
                <div class="font-medium">
                    {{ column.name }}
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    {{ column.position }}
                </div>

                <!-- Task list -->
                <ul class="space-y-2 mb-3">
                    <li v-for="task in column.tasks" :key="task.id" class="bg-white rounded-lg border p-3">
                        <p class="font-medium">
                            {{ task.title }}
                        </p>
                        <p v-if="task.description" class="text-sm text-gray-600 whitespace-pre-line">
                            {{ task.description }}
                        </p>

                        <!-- Subtask list -->
                         <ul v-if="task.subtasks?.length" class="mt-2 space-y-1">
                            <li v-for="subtask in task.subtasks" :key="subtask.id" class="flex items-start gap-2">
                                <input type="checkbox" :checked="subtask.is_completed" class="mt-1" @change="updateSubtaskCompleted(props.board.id, column.id, task.id, subtask.id, ($event.target as HTMLInputElement).checked)" />
                                <div>
                                    <p class="text-sm">
                                        {{ subtask.title }}
                                    </p>
                                    <p v-if="subtask.description" class="text-xs text-gray-600 whitespace-pre-line">
                                        {{ subtask.description }}
                                    </p>
                                </div>
                            </li>
                         </ul>
                         <!-- Add Subtask -->
                        <SubtaskAdd 
                            :board-id="props.board.id"
                            :column-id="column.id"
                            :task-id="task.id"
                        />

                    </li>
                </ul>

                <!-- Add Task -->
                <TaskAdd 
                    :board-id="props.board.id"
                    :column-id="column.id"
                />
            </li>
        </ul>

    </div>
    
</template>
