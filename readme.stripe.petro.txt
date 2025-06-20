== Description ==

Căutați clientul după email: Utilizați endpoint-ul de listare a clienților (/v1/customers) cu un filtru pe câmpul de email pentru a găsi clientul dorit.
curl -G https://api.stripe.com/v1/customers \
  -u sk_test_4...: \
  -d email="emailul_clientului@example.com"

Obțineți plățile clientului: După ce ați identificat clientul, utilizați endpoint-ul de listare a plăților (/v1/payment_intents) pentru a obține toate plățile asociate cu acel client.
curl -G https://api.stripe.com/v1/payment_intents \
  -u sk_test_4...: \
  -d customer="cus_123456789"
