import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useContacts = defineStore('contacts', {
  state: () => ({
    contacts: [],
    selected: null,
    loading: false,
    searchQuery: '',
  }),

  getters: {
    filteredContacts: (state) => {
      if (!state.searchQuery) return state.contacts;
      const q = state.searchQuery.toLowerCase();
      return state.contacts.filter(c => 
        c.canonical_name?.toLowerCase().includes(q) ||
        c.email?.toLowerCase().includes(q)
      );
    },
  },

  actions: {
    async fetchContacts() {
      this.loading = true;
      try {
        const { data } = await axios.get('/api/v1/contacts');
        this.contacts = data;
      } finally {
        this.loading = false;
      }
    },

    selectContact(id) {
      this.selected = this.contacts.find(c => c.id === id) || null;
    },

    async addContact(data) {
      // Optimistic add
      const tempId = 'temp-' + Date.now();
      const optimisticContact = { ...data, id: tempId, _optimistic: true };
      this.contacts.unshift(optimisticContact);
      try {
        const { data: created } = await axios.post('/api/v1/contacts', data);
        const index = this.contacts.findIndex(c => c.id === tempId);
        if (index > -1) this.contacts.splice(index, 1, created);
        return created;
      } catch (error) {
        // Revert on error
        const index = this.contacts.findIndex(c => c.id === tempId);
        if (index > -1) this.contacts.splice(index, 1);
        throw error;
      }
    },

    async updateContact(id, data) {
      const index = this.contacts.findIndex(c => c.id === id);
      if (index > -1) {
        const original = { ...this.contacts[index] };
        this.contacts[index] = { ...this.contacts[index], ...data };
        try {
          await axios.put(`/api/v1/contacts/${id}`, data);
        } catch (error) {
          this.contacts[index] = original;
          throw error;
        }
      }
    },

    async deleteContact(id) {
      const index = this.contacts.findIndex(c => c.id === id);
      if (index > -1) {
        const original = this.contacts[index];
        this.contacts.splice(index, 1);
        try {
          await axios.delete(`/api/v1/contacts/${id}`);
        } catch (error) {
          this.contacts.splice(index, 0, original);
          throw error;
        }
      }
    },

    setSearchQuery(query) {
      this.searchQuery = query;
    },
  },
});
