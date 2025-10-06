<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'

type Board = {
    id: number
    name: string
    position: number
    created_at: string
}

const props = defineProps<{ boards: Board[] }>()

const form = useForm({ name: '' })
function submit(){
    form.post('/boards', {
        onSuccess: () => form.reset('name'),
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Boards" />
    <div class="p-6">
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
            <li v-for="b in props.boards" :key="b.id" class="rounded-2xl border bg-white p-4 shadow-sm text-black">
                <div class="font-medium">
                    {{ b.name }}
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    Created {{ new Date(b.created_at).toLocaleDateString() }}
                </div>
            </li>
        </ul>

    </div>
</template>