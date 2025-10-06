<script setup lang="ts">
type Board = {
    id: number
    name: string
    position: number
    created_at: string
}

const props = defineProps<{ boards: Board[] }>()
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-semibold">Boards page</h1>

        <p v-if="props.boards.length === 0" class="text-gray-600">
            You don't have any boards yet.
        </p>

        <ul v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap4">
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