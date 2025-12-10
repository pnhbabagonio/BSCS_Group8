<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import {
    BookOpen,
    ChevronDown,
    ChevronUp,
    Download,
    ExternalLink,
    FileText,
    HelpCircle,
    MessageCircle,
    MessageSquare,
    Phone,
    Search,
    Upload,
    Video,
    QrCode,
    CreditCard,
    Users,
    Calendar,
} from 'lucide-vue-next';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Help & Support', href: '/help-support' },
];

// FAQ data
const faqs = ref([
    { question: 'How do I generate a QR code for payment collection?', answer: 'To generate a QR code: Navigate to the QR Generator page from the Quick Actions menu, enter the payment amount and description, click "Generate QR Code", then download or share the generated QR code.', open: false },
    { question: 'How can I manually record a payment?', answer: 'To manually record a payment: Go to the Record Payment page from Quick Actions, select the member, enter the payment details (amount, date, payment method), add any notes or references, then click "Record Payment" to save.', open: false },
    { question: 'Why is my QR code not scanning properly?', answer: 'Common reasons for scanning issues: The QR code is too small or pixelated when printed, poor lighting conditions when scanning, the camera lens is dirty or damaged, outdated scanning app or system software. Try generating a new QR code with higher resolution settings.', open: false },
    { question: 'How do I add a new member to the system?', answer: 'To add a new member: Click "Add Member" from the Quick Actions menu, fill in the required information (name, contact details, etc.), set the member\'s status and permissions, upload a profile photo if available, then click "Save" to add the member to the database.', open: false },
    { question: 'How can I view transaction history for a member?', answer: 'To view transaction history: Go to the Members section from the main navigation, search for the specific member, click on the member\'s name to view their profile, navigate to the "Transactions" tab, then you can filter transactions by date range or type.', open: false }
]);

const toggleFaq = (index: number) => { faqs.value[index].open = !faqs.value[index].open; };

// Support form data
const formData = ref({
    name: '',
    email: '',
    subject: '',
    category: '',
    priority: 'medium',
    description: '',
    attachments: [] as File[]
});

// Handle file upload
const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        formData.value.attachments = Array.from(target.files);
    }
};

// Submit support request to backend
const submitSupportRequest = () => {
    // basic client-side validation
    if (!formData.value.subject.trim()) { alert('Please enter a subject'); return; }
    if (!formData.value.category) { alert('Please select a category'); return; }
    if (!formData.value.description.trim()) { alert('Please enter a description'); return; }

    const payload = new FormData();
    payload.append('subject', formData.value.subject);
    payload.append('message', formData.value.description);
    payload.append('category', formData.value.category);
    payload.append('priority', formData.value.priority);
    // optional fields
    if (formData.value.name) payload.append('name', formData.value.name);
    if (formData.value.email) payload.append('email', formData.value.email);

    formData.value.attachments.forEach((file, idx) => {
        payload.append('attachments[]', file, file.name);
    });

    router.post('/help-support', payload, {
        preserveScroll: false,
        preserveState: false,
        onStart: () => { /* you can set a loading flag here */ },
        onSuccess: () => {
            alert('Support request submitted successfully!');
            // reset
            formData.value = { name: '', email: '', subject: '', category: '', priority: 'medium', description: '', attachments: [] };
        },
        onError: (errors: any) => {
            console.error('Validation errors', errors);
            // Inertia will also populate page.props.errors; you can surface them in UI.
        }
    });
};
</script>

<template>

    <Head title="Help & Support" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">PSITS Nexus Help & Support</h1>
                    <p class="text-muted-foreground">Find answers to common questions and get assistance</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" placeholder="Search help articles..."
                            class="pl-10 pr-4 py-2 border border-border rounded-lg w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-card">
                        <Search class="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground" />
                    </div>
                </div>
            </div>

            <div class="grid gap-6">
                <!-- Quick Help Cards -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        class="rounded-xl border border-border bg-card p-6 cursor-pointer hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="rounded-full bg-blue-100 p-3 text-blue-600">
                                <QrCode class="h-6 w-6" />
                            </div>
                            <h3 class="font-semibold">QR Payments</h3>
                        </div>
                        <p class="text-sm text-muted-foreground">Learn how to generate and scan QR codes for payments
                        </p>
                    </div>

                    <div
                        class="rounded-xl border border-border bg-card p-6 cursor-pointer hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="rounded-full bg-green-100 p-3 text-green-600">
                                <CreditCard class="h-6 w-6" />
                            </div>
                            <h3 class="font-semibold">Payment Issues</h3>
                        </div>
                        <p class="text-sm text-muted-foreground">Troubleshoot payment recording and processing problems
                        </p>
                    </div>

                    <div
                        class="rounded-xl border border-border bg-card p-6 cursor-pointer hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="rounded-full bg-purple-100 p-3 text-purple-600">
                                <Users class="h-6 w-6" />
                            </div>
                            <h3 class="font-semibold">Member Management</h3>
                        </div>
                        <p class="text-sm text-muted-foreground">Add, edit, and manage member information</p>
                    </div>

                    <div
                        class="rounded-xl border border-border bg-card p-6 cursor-pointer hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="rounded-full bg-orange-100 p-3 text-orange-600">
                                <Calendar class="h-6 w-6" />
                            </div>
                            <h3 class="font-semibold">Events & Calendar</h3>
                        </div>
                        <p class="text-sm text-muted-foreground">Create events and manage the organization calendar</p>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="rounded-xl border border-border bg-card p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="text-xl font-semibold">Frequently Asked Questions</h2>
                        <HelpCircle class="h-5 w-5 text-muted-foreground" />
                    </div>

                    <div class="space-y-4">
                        <div v-for="(faq, index) in faqs" :key="index" class="border-b border-border pb-4">
                            <button class="flex w-full items-center justify-between text-left font-medium"
                                @click="toggleFaq(index)">
                                <span>{{ faq.question }}</span>
                                <ChevronDown v-if="!faq.open" class="h-5 w-5 text-muted-foreground" />
                                <ChevronUp v-else class="h-5 w-5 text-muted-foreground" />
                            </button>
                            <div v-if="faq.open" class="mt-2 text-muted-foreground">
                                <p>{{ faq.answer }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Support Section -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Contact Form -->
                    <div class="rounded-xl border border-border bg-card p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xl font-semibold">Contact Support</h2>
                            <MessageCircle class="h-5 w-5 text-muted-foreground" />
                        </div>

                        <form class="space-y-4" @submit.prevent="submitSupportRequest">
                            <div>
                                <label class="block text-sm font-medium mb-1">Name</label>
                                <input type="text" v-model="formData.name"
                                    class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-card">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input type="email" v-model="formData.email"
                                    class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-card">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Subject</label>
                                <input type="text" v-model="formData.subject"
                                    class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-card"
                                    required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Category</label>
                                <select v-model="formData.category"
                                    class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-card"
                                    required>
                                    <option value="">Select a category</option>
                                    <option value="billing">Payment Issues</option>
                                    <option value="qr">QR Code Problems</option>
                                    <option value="account">Member Management</option>
                                    <option value="events">Event Management</option>
                                    <option value="technical">Technical Support</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Description</label>
                                <textarea rows="4" v-model="formData.description"
                                    class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-card"
                                    required></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Attachments (optional)</label>
                                <div class="flex items-center justify-center w-full">
                                    <label
                                        class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer border-border bg-muted/50 hover:bg-muted/70 transition-colors">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <Upload class="w-8 h-8 mb-3 text-muted-foreground" />
                                            <p class="mb-2 text-sm text-muted-foreground">Click to upload or drag and
                                                drop</p>
                                            <p class="text-xs text-muted-foreground"
                                                v-if="formData.attachments.length > 0">
                                                {{ formData.attachments.length }} file(s) selected
                                            </p>
                                        </div>
                                        <input type="file" class="hidden" @change="handleFileUpload" multiple />
                                    </label>
                                </div>
                            </div>

                            <Button type="submit" class="w-full">Submit Request</Button>
                        </form>
                    </div>

                    <!-- Support Resources -->
                    <div class="rounded-xl border border-border bg-card p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xl font-semibold">Support Resources</h2>
                            <BookOpen class="h-5 w-5 text-muted-foreground" />
                        </div>

                        <div class="space-y-4">
                            <div class="p-4 border border-border rounded-lg">
                                <h3 class="font-medium flex items-center gap-2 mb-2">
                                    <FileText class="h-4 w-4 text-blue-600" />
                                    User Guide & Documentation
                                </h3>
                                <p class="text-sm text-muted-foreground mb-3">Comprehensive guide to all PSITS Nexus
                                    features</p>
                                <button class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                                    Download PDF
                                    <Download class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="p-4 border border-border rounded-lg">
                                <h3 class="font-medium flex items-center gap-2 mb-2">
                                    <Video class="h-4 w-4 text-purple-600" />
                                    Video Tutorials
                                </h3>
                                <p class="text-sm text-muted-foreground mb-3">Watch step-by-step tutorials on YouTube
                                </p>
                                <button class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                                    Visit Channel
                                    <ExternalLink class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="p-4 border border-border rounded-lg">
                                <h3 class="font-medium flex items-center gap-2 mb-2">
                                    <MessageSquare class="h-4 w-4 text-green-600" />
                                    Community Forum
                                </h3>
                                <p class="text-sm text-muted-foreground mb-3">Connect with other PSITS Nexus users</p>
                                <button class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                                    Join Discussion
                                    <ExternalLink class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="p-4 border border-border rounded-lg">
                                <h3 class="font-medium flex items-center gap-2 mb-2">
                                    <Phone class="h-4 w-4 text-orange-600" />
                                    Direct Support
                                </h3>
                                <p class="text-sm text-muted-foreground mb-1">Email: support@psitsnexus.ph</p>
                                <p class="text-sm text-muted-foreground mb-3">Phone: +63 (02) 1234-5678</p>
                                <p class="text-xs text-muted-foreground">Available Monday-Friday, 8:00 AM - 5:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>