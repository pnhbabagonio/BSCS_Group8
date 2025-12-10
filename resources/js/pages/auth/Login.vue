<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Form, Head, Link } from '@inertiajs/vue3';
import { Eye, EyeOff, LoaderCircle, Lock, Mail, MessageCircle } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <AuthBase title="Welcome back" description="Sign in to your account to continue">

        <Head title="Log in" />

        <div v-if="status" class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4">
            <div class="flex items-center">
                <svg class="mr-3 h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="text-sm font-medium text-green-800">{{ status }}</p>
            </div>
        </div>

        <Form method="post" :action="route('login')" :reset-on-success="['password']" v-slot="{ errors, processing }"
            class="space-y-6">
            <div class="space-y-5">
                <!-- Email Field -->
                <div class="space-y-2">
                    <Label for="email" class="text-sm font-semibold text-gray-700 dark:text-gray-300"> Email address
                    </Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <Mail class="h-5 w-5 text-gray-400" />
                        </div>
                        <Input id="email" type="email" name="email" required autofocus :tabindex="1"
                            autocomplete="email" placeholder="Enter your email"
                            class="h-12 border-gray-200 bg-gray-50 pl-12 transition-all duration-200 focus:border-blue-500 focus:bg-white focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:focus:border-blue-400" />
                    </div>
                    <InputError :message="errors.email" />
                </div>

                <!-- Password Field -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-sm font-semibold text-gray-700 dark:text-gray-300"> Password
                        </Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')"
                            class="text-xs font-medium text-blue-600 hover:text-blue-500" :tabindex="5">
                            Forgot password?
                        </TextLink>
                    </div>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <Lock class="h-5 w-5 text-gray-400" />
                        </div>
                        <Input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                            :tabindex="2" autocomplete="current-password" placeholder="Enter your password"
                            class="h-12 border-gray-200 bg-gray-50 pr-12 pl-12 transition-all duration-200 focus:border-blue-500 focus:bg-white focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:focus:border-blue-400" />
                        <button type="button" @click="togglePasswordVisibility"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 transition-colors duration-200 hover:text-gray-600">
                            <Eye v-if="!showPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex cursor-pointer items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Remember me</span>
                    </Label>
                </div>

                <!-- Submit Button -->
                <Button type="submit"
                    class="h-12 w-full transform rounded-lg bg-gradient-to-r from-[#2563eb] to-[#1d4ed8] font-semibold text-white shadow-lg transition-all duration-200 hover:scale-[1.02] hover:from-[#1d4ed8] hover:to-[#1e40af] hover:shadow-xl disabled:transform-none disabled:cursor-not-allowed disabled:opacity-50"
                    :tabindex="4" :disabled="processing">
                    <LoaderCircle v-if="processing" class="mr-2 h-5 w-5 animate-spin" />
                    <Lock v-else class="mr-2 h-5 w-5" />
                    {{ processing ? 'Signing in...' : 'Sign in' }}
                </Button>
            </div>

            <!-- Sign Up Link -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-white px-4 text-gray-500 dark:bg-gray-800">or</span>
                </div>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Don't have an account?
                    <TextLink :href="route('register')" :tabindex="5"
                        class="ml-1 font-semibold text-blue-600 hover:text-blue-500">
                        Create account
                    </TextLink>
                </p>
            </div>

            <!-- Security Notice -->
            <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                <div class="flex items-start">
                    <svg class="mt-0.5 mr-3 h-5 w-5 text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300">Secure Login</h4>
                        <p class="mt-1 text-xs text-blue-700 dark:text-blue-400">
                            Your connection is encrypted and secured with industry-standard protocols.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Support Link -->
            <div class="mt-4 text-center">
                <Link :href="route('contact-support')"
                    class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center gap-1">
                    <MessageCircle class="h-4 w-4" />
                    Contact Support
                </Link>
            </div>
        </Form>
    </AuthBase>
</template>
