import { defineStore } from 'pinia'

export const useLabelsStore = defineStore('labels', {
  state: () => ({
    shipments: [
      {
        sender: {
          name: 'muh hee ow',
          phoneCode: null,
          phoneNumber: null,
          email: 'singpostproj@gmail.com',
          line1: 'aow',
          line2: null,
          line3: null,
          city: 'muh hee ow city',
          state: 'state',
          postalCode: 'one',
          country: 'SG',
        },
        recipient: {
          name: 'evil muh hee ow',
          phoneCode: null,
          phoneNumber: null,
          email: 'singpostproj@gmail.com',
          line1: 'aaa',
          line2: null,
          line3: null,
          city: 'evil city',
          state: 'evil state',
          postalCode: 'two',
          country: 'AU',
        },
        service: {
          name: 'Basic Mail',
          isTracked: false,
          min: 5,
          max: 24,
          price: '35.85',
          selected: true,
        },
        items: {
          1: {
            costSGD: 1,
            itemCurrency: 'SGD',
            itemDescription: 'a',
            itemQuantity: 1,
            itemValue: 1,
            itemWeight: 1,
          },
        },
        refNumbers: {},
        dimensions: { weight: 1, height: 1, length: 1, width: 1 },
        complete: true,
        selected: false,
        totalCostSGD: 1,
        type: 'Documents',
        mailId: 1,
      },
    ],
  }),
  getters: {},
  actions: {},
})
