<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-10" @close="closeModal">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild as="template" enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel
              class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
              <!-- Header -->
              <div class="mb-6">
                <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900">
                  {{ isEditing ? 'Edit Event' : 'Create New Event' }}
                </DialogTitle>
              </div>

              <!-- Form -->
              <form @submit.prevent="submitForm">
                <div class="space-y-4">
                  <!-- Title -->
                  <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Event Title</label>
                    <input v-model="form.title" type="text" id="title" required
                      class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500" />
                    <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
                  </div>

                  <!-- Description -->
                  <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea v-model="form.description" id="description" rows="3" required
                      class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"></textarea>
                    <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                  </div>

                  <!-- Date and Time -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                      <input v-model="form.date" type="date" id="date" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500" />
                      <p v-if="errors.date" class="mt-1 text-sm text-red-600">{{ errors.date }}</p>
                    </div>
                    <div>
                      <label for="time" class="block text-sm font-medium text-gray-700">Time</label>
                      <input v-model="form.time" type="time" id="time" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500" />
                      <p v-if="errors.time" class="mt-1 text-sm text-red-600">{{ errors.time }}</p>
                    </div>
                  </div>

                  <!-- Venue and Address -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="venue" class="block text-sm font-medium text-gray-700">Venue</label>
                      <input v-model="form.venue" type="text" id="venue" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500" />
                      <p v-if="errors.venue" class="mt-1 text-sm text-red-600">{{ errors.venue }}</p>
                    </div>
                    <div>
                      <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                      <input v-model="form.address" type="text" id="address" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500" />
                      <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address }}</p>
                    </div>
                  </div>

                  <!-- Capacity and Organizer -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="max_capacity" class="block text-sm font-medium text-gray-700">Max Capacity</label>
                      <input v-model="form.max_capacity" type="number" id="max_capacity" min="1" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500" />
                      <p v-if="errors.max_capacity" class="mt-1 text-sm text-red-600">{{ errors.max_capacity }}</p>
                    </div>
                    <div>
                      <label for="organizer" class="block text-sm font-medium text-gray-700">Organizer</label>
                      <input v-model="form.organizer" type="text" id="organizer" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500" />
                      <p v-if="errors.organizer" class="mt-1 text-sm text-red-600">{{ errors.organizer }}</p>
                    </div>
                  </div>

                  <!-- Status and Registration Deadline -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                      <select v-model="form.status" id="status" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                        <option value="Upcoming">Upcoming</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                      </select>
                      <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status }}</p>
                    </div>
                    <div>
                      <label for="registration_deadline" class="block text-sm font-medium text-gray-700">
                        Registration Deadline
                      </label>
                      <input v-model="form.registration_deadline" type="datetime-local" id="registration_deadline"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500" />
                      <p v-if="errors.registration_deadline" class="mt-1 text-sm text-red-600">{{
                        errors.registration_deadline }}</p>
                    </div>
                  </div>

                  <!-- Image URL -->
                  <div>
                    <label for="image_url" class="block text-sm font-medium text-gray-700">Image URL (Optional)</label>
                    <input v-model="form.image_url" type="url" id="image_url"
                      class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500" />
                    <p v-if="errors.image_url" class="mt-1 text-sm text-red-600">{{ errors.image_url }}</p>
                  </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-6 flex justify-end space-x-3">
                  <button type="button" @click="closeModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Cancel
                  </button>
                  <button type="submit" :disabled="loading" :class="[
                    'px-4 py-2 text-sm font-medium text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500',
                    loading ? 'bg-blue-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'
                  ]">
                    {{ loading ? 'Saving...' : (isEditing ? 'Update Event' : 'Create Event') }}
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  event: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

// Reactive data
const loading = ref(false)
const errors = ref({})

const form = ref({
  title: '',
  description: '',
  date: '',
  time: '',
  venue: '',
  address: '',
  status: 'Upcoming',
  max_capacity: 50,
  organizer: '',
  image_url: '',
  registration_deadline: ''
})

// Computed properties
const isEditing = computed(() => !!props.event)

// Methods
const closeModal = () => {
  emit('close')
  resetForm()
}

const resetForm = () => {
  form.value = {
    title: '',
    description: '',
    date: '',
    time: '',
    venue: '',
    address: '',
    status: 'Upcoming',
    max_capacity: 50,
    organizer: '',
    image_url: '',
    registration_deadline: ''
  }
  errors.value = {}
}

const submitForm = async () => {
  loading.value = true
  errors.value = {}

  try {
    const url = isEditing.value ? `/events/${props.event.id}` : '/events'
    const method = isEditing.value ? 'PUT' : 'POST'

    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(form.value)
    })

    if (response.ok) {
      emit('saved')
      closeModal()
    } else {
      const data = await response.json()
      if (data.errors) {
        errors.value = data.errors
      } else {
        alert(data.message || 'Failed to save event')
      }
    }
  } catch (error) {
    console.error('Form submission failed:', error)
    alert('Failed to save event')
  } finally {
    loading.value = false
  }
}

// Watchers
watch(() => props.show, (show) => {
  if (show && props.event) {
    // Populate form for editing
    form.value = {
      title: props.event.title,
      description: props.event.description,
      date: props.event.date,
      time: props.event.time,
      venue: props.event.venue,
      address: props.event.address,
      status: props.event.status,
      max_capacity: props.event.max_capacity,
      organizer: props.event.organizer,
      image_url: props.event.image_url || '',
      registration_deadline: props.event.registration_deadline || ''
    }
  }
})
</script>