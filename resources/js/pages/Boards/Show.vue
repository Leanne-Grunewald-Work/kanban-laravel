<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

type Column = {
    id: number
    name: string
    position: number
    created_at: string
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
            </li>
        </ul>

    </div>
</template>