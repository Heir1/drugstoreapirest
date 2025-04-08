<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashJournal extends Model
{
    use HasFactory;

    /**
     * Les champs qui peuvent être assignés massivement.
     *
     * @var array<string>
     */
    protected $fillable = [
        'transaction_type', // Type de transaction (income ou expense)
        'amount', // Montant de la transaction
        'description', // Description facultative
        'currency_id', // Clé étrangère vers la table des devises
        'created_by', // ID de l'utilisateur qui a créé l'entrée
        'updated_by', // ID de l'utilisateur qui a mis à jour l'entrée
        'transaction_date',
        'ticket_counter',
    ];

    /**
     * Relation avec l'utilisateur qui a créé l'entrée.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a mis à jour l'entrée.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relation avec la devise associée.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function ticketCounterUser()
    {
        return $this->belongsTo(User::class, 'ticket_counter');
    }

}
