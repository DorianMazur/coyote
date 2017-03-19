<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Firm;
use Coyote\Services\FormBuilder\Form;

class InvoiceForm extends Form
{
    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'label' => 'Nazwa firmy',
                'rules' => 'string|max:200|required_with:enable_invoice'
            ])
            ->add('vat_id', 'text', [
                'label' => 'NIP (opcjonalnie)',
                'rules' => 'string'
            ])
            ->add('address', 'text', [
                'rules' => 'string|required_with:enable_invoice',
                'label' => 'Adres',
            ])
            ->add('city', 'text', [
                'rules' => 'string|required_with:enable_invoice',
                'label' => 'Miejscowość',
            ])
            ->add('postal_code', 'text', [
                'rules' => 'string|required_with:enable_invoice',
                'label' => 'Kod pocztowy',
            ]);

        if (!empty($this->data) && !$this->isSubmitted() && $this->data instanceof Firm) {
            $this->get('address')->setValue($this->data->street . ' ' . $this->data->house);
        }
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'nazwa',
            'vat_id' => 'NIP'
        ];
    }
}