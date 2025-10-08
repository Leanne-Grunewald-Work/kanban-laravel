<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{ 
    boardId: number 
    columnId: number
 }>()

const form = useForm({ title: '', description: '', due_date: ''})

function submit(){
    form.post(`/boards/${props.boardId}/columns/${props.columnId}/tasks`, {
        onSuccess: () => form.reset('title', 'description', 'due_date'),
        preserveScroll: true,
    })
}

</script>

<template>
    <form @submit.prevent="submit" class="space-y-2 mt-3">
        <input v-model="form.title" type="text" placeholder="Task title"
                class="w-full rounded-lg border px-3 py-2" />
        <textarea v-model="form.description" rows="2" placeholder="Description (optional)"
                    class="w-full rounded-lg border px-3 py-2"></textarea>
        <input v-model="form.due_date" type="date"
                class="w-full rounded-lg border px-3 py-2" />
        <button :disabled="form.processing"
                class="w-full rounded-lg bg-black text-white px-3 py-2">
            Add Task
        </button>
        <div v-if="form.errors.title" class="text-red-600 text-sm">{{ form.errors.title }}</div>
        <div v-if="form.errors.due_date" class="text-red-600 text-sm">{{ form.errors.due_date }}</div>
    </form>
</template>