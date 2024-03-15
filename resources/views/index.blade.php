<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>
        <title>BTC API</title>

        <script>
            let currentTab = '';

            function fetchTransactions() {
                currentTab = 'transactions';

                fetch('/api/transactions')
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('transactionsContainer');
                        container.innerHTML = '';
                        document.getElementById('balanceContainer').innerHTML = '';
                        document.getElementById('newTransactionContainer').innerHTML = '';

                        document.getElementById('transactionButton').classList.add('bg-blue-200');
                        document.getElementById('transactionButton').classList.remove('bg-white');
                        document.getElementById('balanceButton').classList.add('bg-white');
                        document.getElementById('balanceButton').classList.remove('bg-blue-200');
                        document.getElementById('newTransactionButton').classList.add('bg-white');
                        document.getElementById('newTransactionButton').classList.remove('bg-blue-200');

                        if(Array.isArray(data) && data.length) {
                            data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                            const table = document.createElement('table');
                            table.classList.add('table-auto', 'w-full', 'text-left', 'shadow-md', 'mt-2');
                            const thead = document.createElement('thead');
                            thead.classList.add('bg-gray-200');
                            thead.innerHTML = `
                                <tr>
                                    <th class="px-4 py-2">Transaction ID</th>
                                    <th class="px-4 py-2">Amount (BTC)</th>
                                    <th class="px-4 py-2">Spent</th>
                                    <th class="px-4 py-2">Date</th>
                                </tr>
                            `;
                            table.appendChild(thead);

                            const tbody = document.createElement('tbody');
                            data.forEach((transaction, index) => {
                                const row = document.createElement('tr');
                                row.classList.add(index % 2 === 0 ? 'bg-white' : 'bg-gray-100');
                                row.innerHTML = `
                                    <td class="border px-4 py-2">${transaction.transaction_id}</td>
                                    <td class="border px-4 py-2">${transaction.amount_btc}</td>
                                    <td class="border px-4 py-2">${transaction.spent}</td>
                                    <td class="border px-4 py-2">${transaction.created_at}</td>
                                `;
                                tbody.appendChild(row);
                            });
                            table.appendChild(tbody);

                            container.appendChild(table);
                        } else {
                            container.textContent = 'No transactions found.';
                        }
                    })
                    .catch(error => {
                        document.getElementById('transactionsContainer').textContent = 'Failed to fetch transactions.';
                    });
            }
            function fetchBalance() {
                currentTab = 'balance';

                function sendReq() {
                    fetch('/api/balance')
                        .then(response => response.json())
                        .then(data => {
                            updateBalance(data);
                        })
                        .catch(error => {
                            document.getElementById('balanceContainer').textContent = 'Failed to fetch balance.';
                        });
                }

                sendReq();

                setInterval(sendReq, 3000);
            }
            function newTransaction() {
                currentTab = 'newTransaction';

                const container = document.getElementById('newTransactionContainer');
                document.getElementById('transactionsContainer').innerHTML = '';
                document.getElementById('balanceContainer').innerHTML = '';

                document.getElementById('balanceButton').classList.add('bg-white');
                document.getElementById('balanceButton').classList.remove('bg-blue-200');
                document.getElementById('transactionButton').classList.add('bg-white');
                document.getElementById('transactionButton').classList.remove('bg-blue-200');
                document.getElementById('newTransactionButton').classList.add('bg-blue-200');
                document.getElementById('newTransactionButton').classList.remove('bg-white');

                container.innerHTML = `            <form id="transactionForm" class="border p-2 rounded">
                <div class="mb-4">
                    <label for="btcAmount" class="text-gray-700 text-sm font-bold mb-2">EUR Amount</label>
                    <input type="number" id="eurAmount" name="btcAmount" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                </div>
                <div class="mb-4 flex flex-row space-x-2">
                    <label for="spentBoolean" class="block text-gray-700 text-sm font-bold">Spent</label>
                    <input type="checkbox" id="spentBoolean" name="spentBoolean" class="leading-tight">
                </div>
                <button onclick="sendTransaction(event)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mx-auto w-full">Submit</button>
            </form>
`;
            }
            async function sendTransaction(event) {
                event.preventDefault();

                let eurAmount = document.getElementById('eurAmount').value;
                let spentBoolean = document.getElementById('spentBoolean').checked;

                fetch('/api/transactions', {
                    method: 'post',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        eurAmount: eurAmount,
                        spentBoolean: spentBoolean
                    })
                }).then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('newTransactionContainer');
                        let messageElement = document.getElementById('transactionResponseText');

                        if (!messageElement) {
                            messageElement = document.createElement('h1');
                            messageElement.id = 'transactionResponseText';
                            container.appendChild(messageElement);
                        }

                        messageElement.textContent = data.res;
                        container.appendChild(messageElement);
                    }).catch(error => {
                        console.error('Error:', error);
                        const container = document.getElementById('newTransactionContainer');
                        container.innerHTML = '';
                        const errorMessage = document.createElement('h1');
                        errorMessage.textContent = 'Failed to send transaction.';
                        container.appendChild(errorMessage);
                    });
            }

            function updateBalance(data) {
                if (currentTab !== 'balance') {
                    return;
                }

                const container = document.getElementById('balanceContainer');
                document.getElementById('transactionsContainer').innerHTML = '';
                document.getElementById('balanceContainer').innerHTML = '';
                document.getElementById('newTransactionContainer').innerHTML = '';

                document.getElementById('balanceButton').classList.add('bg-blue-200');
                document.getElementById('balanceButton').classList.remove('bg-white');
                document.getElementById('transactionButton').classList.add('bg-white');
                document.getElementById('transactionButton').classList.remove('bg-blue-200');
                document.getElementById('newTransactionButton').classList.add('bg-white');
                document.getElementById('newTransactionButton').classList.remove('bg-blue-200');

                const table = document.createElement('table');
                table.classList.add('table-auto', 'w-full', 'text-left', 'shadow-md', 'mt-2');

                const thead = document.createElement('thead');
                thead.classList.add('bg-gray-200');
                thead.innerHTML = `
                            <tr>
                                <th class="px-4 py-2">Description</th>
                                <th class="px-4 py-2">Value</th>
                            </tr>
                        `;
                table.appendChild(thead);

                const tbody = document.createElement('tbody');
                tbody.innerHTML = `
                            <tr class="bg-white">
                                <td class="border px-4 py-2">Total BTC</td>
                                <td class="border px-4 py-2">${data.total_btc}</td>
                            </tr>
                            <tr class="bg-gray-100">
                                <td class="border px-4 py-2">EUR/BTC</td>
                                <td class="border px-4 py-2">${data['EUR/BTC'].toLocaleString('en-US', {style: 'currency', currency: 'EUR'})}</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="border px-4 py-2">EUR Value</td>
                                <td class="border px-4 py-2">${data['EUR value'].toLocaleString('en-US', {style: 'currency', currency: 'EUR'})}</td>
                            </tr>
                            <tr class="bg-gray-100">
                                <td class="border px-4 py-2">Timestamp</td>
                                <td class="border px-4 py-2">${data.timestamp}</td>
                            </tr>
                        `;
                table.appendChild(tbody);

                container.appendChild(table);
            }

        </script>
    </head>
    <body class="antialiased flex flex-col h-screen">
        <h1 class="text-6xl font-bold mt-24 mx-auto">BTC Transactions</h1>
        <div id="buttonContainer" class="mx-auto">
            <button id="transactionButton" onclick="fetchTransactions()" class="mx-auto my-4 rounded border p-2">get transactions</button>
            <button id="balanceButton" onclick="fetchBalance()" class="mx-auto my-4 rounded border p-2">get balance</button>
            <button id="newTransactionButton" onclick="newTransaction()" class="mx-auto my-4 rounded border p-2">new transaction</button>
        </div>


        <div id="transactionsContainer" class="mx-auto">

        </div>
        <div id="balanceContainer" class="mx-auto">

        </div>
        <div id="newTransactionContainer" class="mx-auto">

        </div>
    </body>
</html>
