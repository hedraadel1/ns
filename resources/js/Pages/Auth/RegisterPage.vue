<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Logo/Title -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Nexus</h1>
        <p class="text-slate-400">Cognitive Digital Twin Platform</p>
      </div>

      <!-- Register Card -->
      <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/30 rounded-2xl p-8 shadow-2xl">
        <h2 class="text-2xl font-bold text-white mb-6">Create Account</h2>

        <!-- Error Message -->
        <div
          v-if="error"
          class="mb-4 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm"
        >
          {{ error }}
        </div>

        <!-- Form -->
        <form @submit.prevent="handleRegister" class="space-y-4">
          <!-- Name Input -->
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Full Name</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="John Doe"
              class="w-full px-4 py-2 bg-slate-700/50 border border-slate-600/50 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition"
            />
          </div>

          <!-- Email Input -->
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Email Address</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="you@example.com"
              class="w-full px-4 py-2 bg-slate-700/50 border border-slate-600/50 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition"
            />
          </div>

          <!-- Password Input -->
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Password</label>
            <input
              v-model="form.password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full px-4 py-2 bg-slate-700/50 border border-slate-600/50 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition"
            />
          </div>

          <!-- Confirm Password Input -->
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Confirm Password</label>
            <input
              v-model="form.passwordConfirmation"
              type="password"
              required
              placeholder="••••••••"
              class="w-full px-4 py-2 bg-slate-700/50 border border-slate-600/50 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition"
            />
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading || form.password !== form.passwordConfirmation"
            class="w-full mt-6 px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-600/50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition duration-200"
          >
            <span v-if="!isLoading">Create Account</span>
            <span v-else class="flex items-center justify-center">
              <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating account...
            </span>
          </button>
        </form>

        <!-- Login Link -->
        <p class="text-center text-slate-400 text-sm mt-4">
          Already have an account?
          <router-link to="/login" class="text-blue-400 hover:text-blue-300 font-medium">
            Sign in
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/useAuthStore';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',
});

const isLoading = ref(false);
const error = ref(null);

const passwordsMatch = computed(() => form.password === form.passwordConfirmation);

const handleRegister = async () => {
  if (!passwordsMatch.value) {
    error.value = 'Passwords do not match';
    return;
  }

  isLoading.value = true;
  error.value = null;

  const success = await authStore.register(
    form.name,
    form.email,
    form.password,
    form.passwordConfirmation
  );
  
  if (success) {
    router.push('/');
  } else {
    error.value = authStore.error || 'Registration failed. Please try again.';
  }
  
  isLoading.value = false;
};

onMounted(() => {
  // If already authenticated, redirect to home
  if (authStore.isAuthenticated) {
    router.push('/');
  }
});
</script>
