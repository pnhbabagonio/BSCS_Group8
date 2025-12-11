<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { BookOpen, MessageCircle, Mail, Phone, FileText, Video, MessageSquare, Upload } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const formData = ref({
    name: '',
    email: '',
    subject: '',
    category: '',
    priority: 'medium',
    description: '',
    attachments: [] as File[]
});

const isSubmitting = ref(false);

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        formData.value.attachments = Array.from(target.files);
    }
};

const submitSupportRequest = async () => {
    // Basic client-side validation
    if (!formData.value.name.trim()) {
        alert('Please enter your name');
        return;
    }
    if (!formData.value.email.trim()) {
        alert('Please enter your email');
        return;
    }
    if (!formData.value.subject.trim()) {
        alert('Please enter a subject');
        return;
    }
    if (!formData.value.category) {
        alert('Please select a category');
        return;
    }
    if (!formData.value.description.trim()) {
        alert('Please enter a description');
        return;
    }

    isSubmitting.value = true;

    // Create FormData for file upload
    const payload = new FormData();
    payload.append('name', formData.value.name);
    payload.append('email', formData.value.email);
    payload.append('subject', formData.value.subject);
    payload.append('message', formData.value.description);
    payload.append('category', formData.value.category);
    payload.append('priority', formData.value.priority);

    // Append files
    formData.value.attachments.forEach((file) => {
        payload.append('attachments[]', file, file.name);
    });

    // Submit to backend
    router.post('/contact-support', payload, {
        onSuccess: () => {
            alert('Thank you for contacting us! We will get back to you soon.');
            // Reset form
            formData.value = {
                name: '',
                email: '',
                subject: '',
                category: '',
                priority: 'medium',
                description: '',
                attachments: []
            };
            isSubmitting.value = false;
        },
        onError: (errors: any) => {
            console.error('Error submitting form:', errors);
            alert('There was an error submitting your request. Please try again.');
            isSubmitting.value = false;
        }
    });
};
</script>

<template>

    <Head title="Contact Support" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <!-- Navigation Bar -->
        <div class="border-b border-slate-700/50 bg-slate-900/50 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/" class="text-2xl font-bold text-white hover:text-blue-400 transition-colors">
                        PSITS Nexus
                    </Link>
                    <Link href="/login" class="text-sm font-medium text-blue-400 hover:text-blue-300">
                        Back to Login
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-12 text-center">
                <h1 class="text-4xl font-bold text-white mb-4">Get in Touch</h1>
                <p class="text-xl text-slate-300">We're here to help. Contact us with any questions or concerns.</p>
            </div>

            <!-- Content Grid -->
            <div class="grid gap-8 lg:grid-cols-3 mb-12">
                <!-- Contact Form -->
                <div class="lg:col-span-2">
                    <div
                        class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-lg p-8 border border-slate-700/50">
                        <div class="flex items-center gap-2 mb-6">
                            <MessageCircle class="h-6 w-6 text-blue-400" />
                            <h2 class="text-2xl font-bold text-white">Contact Form</h2>
                        </div>

                        <form @submit.prevent="submitSupportRequest" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <Label for="name" class="text-slate-300">Name *</Label>
                                    <Input id="name" v-model="formData.name" type="text" placeholder="Your full name"
                                        class="mt-1 bg-slate-700 border-slate-600 text-white placeholder:text-slate-400"
                                        required />
                                </div>
                                <div>
                                    <Label for="email" class="text-slate-300">Email *</Label>
                                    <Input id="email" v-model="formData.email" type="email" placeholder="your@email.com"
                                        class="mt-1 bg-slate-700 border-slate-600 text-white placeholder:text-slate-400"
                                        required />
                                </div>
                            </div>

                            <div>
                                <Label for="subject" class="text-slate-300">Subject *</Label>
                                <Input id="subject" v-model="formData.subject" type="text"
                                    placeholder="What's this about?"
                                    class="mt-1 bg-slate-700 border-slate-600 text-white placeholder:text-slate-400"
                                    required />
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <Label for="category" class="text-slate-300">Category *</Label>
                                    <select id="category" v-model="formData.category"
                                        class="w-full px-3 py-2 mt-1 bg-slate-700 border border-slate-600 text-white rounded-md focus:outline-none focus:border-blue-500 placeholder:text-slate-400"
                                        required>
                                        <option value="">Select a category</option>
                                        <option value="technical">Technical Issue</option>
                                        <option value="billing">Billing</option>
                                        <option value="account">Account</option>
                                        <option value="general">General Inquiry</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <Label for="priority" class="text-slate-300">Priority</Label>
                                    <select id="priority" v-model="formData.priority"
                                        class="w-full px-3 py-2 mt-1 bg-slate-700 border border-slate-600 text-white rounded-md focus:outline-none focus:border-blue-500 placeholder:text-slate-400">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <Label for="description" class="text-slate-300">Description *</Label>
                                <textarea id="description" v-model="formData.description"
                                    placeholder="Please describe your issue in detail..." rows="6"
                                    class="w-full px-3 py-2 mt-1 bg-slate-700 border border-slate-600 text-white rounded-md focus:outline-none focus:border-blue-500 placeholder:text-slate-400"
                                    required />
                            </div>

                            <div>
                                <Label for="attachments" class="text-slate-300">Attachments (Optional)</Label>
                                <div
                                    class="mt-1 flex justify-center rounded-lg border-2 border-dashed border-slate-600 px-6 py-10 hover:border-slate-500 transition-colors">
                                    <label class="text-center cursor-pointer">
                                        <Upload class="mx-auto h-8 w-8 text-slate-400 mb-2" />
                                        <p class="text-sm text-slate-300">
                                            <span class="font-semibold">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="text-xs text-slate-400 mt-1">PNG, JPG, PDF up to 10MB</p>
                                        <input type="file" class="hidden" multiple @change="handleFileUpload" />
                                    </label>
                                </div>
                                <p v-if="formData.attachments.length > 0" class="text-xs text-slate-400 mt-2">
                                    {{ formData.attachments.length }} file(s) selected
                                </p>
                            </div>

                            <Button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2"
                                :disabled="isSubmitting">
                                {{ isSubmitting ? 'Sending...' : 'Send Message' }}
                            </Button>
                        </form>
                    </div>
                </div>

                <!-- Quick Contact Info -->
                <div class="space-y-6">
                    <div
                        class="bg-gradient-to-br from-blue-900/20 to-slate-800 rounded-lg p-6 border border-slate-700/50">
                        <div class="flex items-start gap-3">
                            <Mail class="h-6 w-6 text-blue-400 flex-shrink-0 mt-1" />
                            <div>
                                <h3 class="font-semibold text-white mb-2">Email</h3>
                                <a href="mailto:support@psitsnexus.ph"
                                    class="text-blue-400 hover:text-blue-300 text-sm break-all">
                                    psitsnexus@gmail.com
                                </a>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gradient-to-br from-green-900/20 to-slate-800 rounded-lg p-6 border border-slate-700/50">
                        <div class="flex items-start gap-3">
                            <Phone class="h-6 w-6 text-green-400 flex-shrink-0 mt-1" />
                            <div>
                                <h3 class="font-semibold text-white mb-2">Phone</h3>
                                <p class="text-slate-300 text-sm">+63 (02) 1234-5678</p>
                                <p class="text-slate-400 text-xs mt-1">Mon-Fri, 8:00 AM - 5:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gradient-to-br from-purple-900/20 to-slate-800 rounded-lg p-6 border border-slate-700/50">
                        <div class="flex items-start gap-3">
                            <BookOpen class="h-6 w-6 text-purple-400 flex-shrink-0 mt-1" />
                            <div>
                                <h3 class="font-semibold text-white mb-2">Resources</h3>
                                <ul class="space-y-2">
                                    <li>
                                        <a href="#" class="text-purple-400 hover:text-purple-300 text-sm">Documentation
                                            →</a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-purple-400 hover:text-purple-300 text-sm">FAQ →</a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-purple-400 hover:text-purple-300 text-sm">Community
                                            Forum →</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
