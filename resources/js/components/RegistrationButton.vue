<template>
    <div>
        <button v-if="!event.is_user_registered && !event.is_user_waitlisted" @click="handleRegister"
            :disabled="!event.can_register" :class="[
                'w-full py-3 px-4 rounded-lg font-medium transition duration-200',
                event.can_register
                    ? 'bg-blue-600 text-white hover:bg-blue-700'
                    : 'bg-gray-400 text-gray-700 cursor-not-allowed'
            ]">
            {{ event.can_register ? 'Register Now' : 'Registration Closed' }}
        </button>

        <button v-else-if="event.is_user_registered" @click="handleCancel" :disabled="!event.can_cancel" :class="[
            'w-full py-3 px-4 rounded-lg font-medium transition duration-200',
            event.can_cancel
                ? 'bg-red-600 text-white hover:bg-red-700'
                : 'bg-gray-400 text-gray-700 cursor-not-allowed'
        ]">
            {{ event.can_cancel ? 'Cancel Registration' : 'Cannot Cancel' }}
        </button>

        <button v-else-if="event.is_user_waitlisted" @click="handleCancel" :disabled="!event.can_cancel" :class="[
            'w-full py-3 px-4 rounded-lg font-medium transition duration-200',
            event.can_cancel
                ? 'bg-orange-600 text-white hover:bg-orange-700'
                : 'bg-gray-400 text-gray-700 cursor-not-allowed'
        ]">
            {{ event.can_cancel ? 'Leave Waitlist' : 'On Waitlist' }}
        </button>

        <!-- Registration deadline notice -->
        <div v-if="event.registration_deadline" class="mt-2 text-xs text-gray-500 text-center">
            Registration closes {{ formatDate(event.registration_deadline) }}
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    event: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['register', 'cancel'])

const handleRegister = () => {
    if (props.event.can_register) {
        emit('register')
    }
}

const handleCancel = () => {
    if (props.event.can_cancel) {
        emit('cancel')
    }
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>