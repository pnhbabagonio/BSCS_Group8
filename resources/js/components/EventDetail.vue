<template>
    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
        <!-- Event Image -->
        <div class="h-48 bg-gray-200 relative">
            <img v-if="event.image_url" :src="event.image_url" :alt="event.title" class="w-full h-full object-cover" />
            <div v-else
                class="w-full h-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                <CalendarIcon class="h-12 w-12 text-white opacity-50" />
            </div>

            <!-- Status Badge -->
            <div class="absolute top-4 right-4">
                <span :class="[
                    'px-3 py-1 rounded-full text-sm font-medium',
                    statusClasses[event.status]
                ]">
                    {{ event.status }}
                </span>
            </div>

            <!-- Capacity Badge -->
            <div class="absolute top-4 left-4 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-sm">
                {{ event.registered_count }}/{{ event.max_capacity }}
            </div>
        </div>

        <!-- Event Content -->
        <div class="p-6">
            <!-- Title and Description -->
            <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">{{ event.title }}</h3>
            <p class="text-gray-600 mb-4 line-clamp-3">{{ event.description }}</p>

            <!-- Event Details -->
            <div class="space-y-2 mb-4">
                <div class="flex items-center text-gray-600">
                    <CalendarIcon class="h-4 w-4 mr-2" />
                    <span>{{ event.formatted_date }} at {{ event.time }}</span>
                </div>
                <div class="flex items-center text-gray-600">
                    <MapPinIcon class="h-4 w-4 mr-2" />
                    <span>{{ event.venue }}, {{ event.address }}</span>
                </div>
                <div class="flex items-center text-gray-600">
                    <UserIcon class="h-4 w-4 mr-2" />
                    <span>Organized by {{ event.organizer }}</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Registration Progress</span>
                    <span>{{ Math.round((event.registered_count / event.max_capacity) * 100) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div :class="[
                        'h-2 rounded-full transition-all duration-500',
                        progressColor
                    ]" :style="{ width: `${Math.min((event.registered_count / event.max_capacity) * 100, 100)}%` }">
                    </div>
                </div>
            </div>

            <!-- Registration Status -->
            <div v-if="event.is_user_registered || event.is_user_waitlisted"
                class="mb-4 p-3 rounded-lg bg-blue-50 border border-blue-200">
                <div class="flex items-center">
                    <CheckCircleIcon v-if="event.is_user_registered" class="h-5 w-5 text-green-600 mr-2" />
                    <ClockIcon v-else class="h-5 w-5 text-orange-600 mr-2" />
                    <span class="text-sm font-medium">
                        {{ event.is_user_registered ? 'Registered' : `Waitlisted (#${event.waitlist_position})` }}
                    </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col space-y-2">
                <RegistrationButton :event="event" @register="$emit('register', event)"
                    @cancel="$emit('cancel', event)" />

                <div v-if="event.can_manage" class="flex space-x-2">
                    <button @click="$emit('view-registrants', event)"
                        class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200 text-sm font-medium">
                        View Registrants
                    </button>
                    <button @click="$emit('edit', event)"
                        class="bg-yellow-600 text-white py-2 px-4 rounded-lg hover:bg-yellow-700 transition duration-200 text-sm font-medium">
                        Edit
                    </button>
                    <button @click="$emit('delete', event)"
                        class="bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition duration-200 text-sm font-medium">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import {
    CalendarIcon,
    MapPinIcon,
    UserIcon,
    CheckCircleIcon,
    ClockIcon
} from '@heroicons/vue/24/outline'
import RegistrationButton from './RegistrationButton.vue'

const props = defineProps({
    event: {
        type: Object,
        required: true
    }
})

defineEmits(['register', 'cancel', 'view-registrants', 'edit', 'delete'])

// Status and progress styling
const statusClasses = {
    'Upcoming': 'bg-green-100 text-green-800',
    'Ongoing': 'bg-blue-100 text-blue-800',
    'Completed': 'bg-gray-100 text-gray-800'
}

const progressColor = computed(() => {
    const percentage = (props.event.registered_count / props.event.max_capacity) * 100
    if (percentage < 70) return 'bg-green-500'
    if (percentage < 90) return 'bg-yellow-500'
    return 'bg-red-500'
})
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 2;
    /* Standard property */
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 3;
    /* Standard property */
}
</style>