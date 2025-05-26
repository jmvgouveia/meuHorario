<div>
    @if ($visible)
    <div
        x-data="{ open: @entangle('visible') }"
        x-show="open"
        class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
        <div
            x-show="open"
            x-transition
            class="bg-white rounded-lg p-6 max-w-lg w-full">
            <h2 class="text-xl font-bold mb-4">Justificação para pedido de troca</h2>

            <form wire:submit.prevent="submit">
                <textarea
                    wire:model.defer="justification"
                    class="w-full border rounded p-2"
                    placeholder="Insira a razão para o pedido de troca"
                    rows="5"></textarea>
                @error('justification') <p class="text-red-600">{{ $message }}</p> @enderror

                <!-- Se quiseres, coloca campos para id_subject e turno aqui -->

                <div class="mt-4 flex justify-end space-x-2">
                    <button
                        type="button"
                        @click="open = false; @this.set('visible', false)"
                        class="px-4 py-2 bg-gray-300 rounded">Cancelar</button>

                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded">Submeter</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>