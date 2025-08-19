import type { FiltersPanelProps } from './types';

export default function FiltersPanel({
    filterLocation,
    setFilterLocation,
    locationsList,
    filterGender,
    setFilterGender,
    interestsList,
    filterInterests,
    setFilterInterests,
}: FiltersPanelProps) {
    return (
        <div
            className="animate-slideDown border-b border-gray-700 bg-gray-800 p-4 shadow-lg"
            style={{ width: '100%' }}
        >
            <div className="space-y-4">
                {/* Filtro de Localização */}
                <div>
                    <label className="mb-2 block text-sm font-medium">
                        Localização
                    </label>
                    <select
                        value={filterLocation}
                        onChange={(e) => setFilterLocation(e.target.value)}
                        className="w-full rounded-lg bg-gray-700 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                    >
                        <option value="">Todos locais</option>
                        {locationsList.map((location) => (
                            <option key={location} value={location}>
                                {location}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Filtro de Gênero */}
                <div>
                    <label className="mb-2 block text-sm font-medium">
                        Gênero
                    </label>
                    <div className="grid grid-cols-3 gap-2">
                        {['all', 'male', 'female'].map((gender) => (
                            <button
                                key={gender}
                                type="button"
                                aria-pressed={filterGender === gender}
                                onClick={() => setFilterGender(gender)}
                                className={`rounded-lg py-2 text-sm transition-colors ${
                                    filterGender === gender
                                        ? 'bg-purple-600'
                                        : 'bg-gray-700 hover:bg-gray-600'
                                }`}
                            >
                                {gender === 'all'
                                    ? 'Todos'
                                    : gender === 'male'
                                      ? 'Homens'
                                      : 'Mulheres'}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Filtro de Interesses */}
                <div>
                    <label className="mb-2 block text-sm font-medium">
                        Interesses
                    </label>
                    <div className="flex flex-wrap gap-2">
                        {interestsList.map((interest) => {
                            const isSelected =
                                filterInterests.includes(interest);
                            return (
                                <button
                                    key={interest}
                                    type="button"
                                    aria-pressed={isSelected}
                                    onClick={() =>
                                        isSelected
                                            ? setFilterInterests(
                                                  filterInterests.filter(
                                                      (i) => i !== interest,
                                                  ),
                                              )
                                            : setFilterInterests([
                                                  ...filterInterests,
                                                  interest,
                                              ])
                                    }
                                    className={`rounded-full px-3 py-1 text-xs transition-colors ${
                                        isSelected
                                            ? 'bg-purple-600'
                                            : 'bg-gray-700 hover:bg-gray-600'
                                    }`}
                                >
                                    {interest}
                                </button>
                            );
                        })}
                    </div>
                </div>
            </div>
        </div>
    );
}
