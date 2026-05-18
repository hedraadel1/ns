<template>
  <div :class="['input-wrapper', { 'input-error': error }]">
    <label v-if="label" :for="inputId" class="input-label">
      {{ label }}
      <span v-if="required" class="input-required">*</span>
    </label>
    <div class="input-container">
      <span v-if="$slots.prepend" class="input-prepend">
        <slot name="prepend"></slot>
      </span>
      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        class="input-field"
        @input="onInput"
        @focus="onFocus"
        @blur="onBlur"
      />
      <span v-if="$slots.append" class="input-append">
        <slot name="append"></slot>
      </span>
    </div>
    <p v-if="error" class="input-error-text">{{ error }}</p>
    <p v-else-if="hint" class="input-hint">{{ hint }}</p>
  </div>
</template>

<script setup>
import { computed, defineProps, defineEmits } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'text',
  },
  placeholder: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
  id: {
    type: String,
    default: null,
  },
})

defineEmits(['update:modelValue', 'focus', 'blur'])

const inputId = computed(() => props.id || `input-${Math.random().toString(36).substr(2, 9)}`)

function onInput(e) {
  props.$emit('update:modelValue', e.target.value)
}

function onFocus(e) {
  props.$emit('focus', e)
}

function onBlur(e) {
  props.$emit('blur', e)
}
</script>

<style scoped>
.input-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.input-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-secondary);
}

.input-required {
  color: var(--color-error);
  margin-left: 0.25rem;
}

.input-container {
  display: flex;
  align-items: center;
  background: var(--color-bg-tertiary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
}

.input-container:focus-within {
  border-color: var(--color-border-focus);
  box-shadow: 0 0 0 3px var(--color-primary-muted);
}

.input-field {
  flex: 1;
  padding: 0.625rem 0.875rem;
  background: transparent;
  border: none;
  color: var(--color-text-primary);
  font-size: 0.875rem;
  outline: none;
}

.input-field::placeholder {
  color: var(--color-text-disabled);
}

.input-field:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.input-prepend,
.input-append {
  padding: 0 0.75rem;
  color: var(--color-text-muted);
  font-size: 0.875rem;
}

.input-prepend {
  border-right: 1px solid var(--color-border);
}

.input-append {
  border-left: 1px solid var(--color-border);
}

.input-error .input-container {
  border-color: var(--color-error);
}

.input-error .input-container:focus-within {
  box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.1);
}

.input-error-text {
  font-size: 0.75rem;
  color: var(--color-error);
}

.input-hint {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}
</style>
